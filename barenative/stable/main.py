import sys
import os
import uuid
import subprocess
import platform
import socket
import logging
import json
import threading
import zipfile
import base64
import shutil
import requests
from pathlib import Path
from waitress import serve
from flask import Flask, request, jsonify
from PyQt6.QtWidgets import (
    QApplication, QMainWindow, QWidget, QHBoxLayout, QVBoxLayout, 
    QPushButton, QLabel, QStackedWidget, QListWidget,
    QLineEdit, QFrame, QCheckBox, QMessageBox, QSystemTrayIcon, QMenu,
    QFileDialog, QProgressBar, QScrollArea, QGridLayout, QGraphicsOpacityEffect
)
from PyQt6.QtGui import QMovie, QIcon, QPixmap, QColor, QAction, QPainter, QFont, QGuiApplication
from PyQt6.QtCore import Qt, QTimer, QObject, pyqtSignal, QThread


# config & process
logging.basicConfig(
    filename='launcher.log',
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

PROFILE_CACHE = {
    "profileTags": "unknown",
    "profileNames": "User",
    "profileBios": "",
    "profileJDates": "",
    "profileAttachs": "",
    "obtained_badges": []
}


def get_base_dir() -> Path:
    if getattr(sys, "frozen", False):
        return Path(sys.executable).parent
    else:
        return Path(__file__).resolve().parent

BASE_DIR = get_base_dir()
CONFIG_FILE = BASE_DIR / "config.json"
CACHE_DIR = BASE_DIR / "cache"
CACHE_DIR.mkdir(parents=True, exist_ok=True)

API_URL_HEARTBEAT = "http://localhost/crossgate/crossgate-community-collection/api/heartbeat.php"
API_URL_AUTH = "http://localhost/crossgate/crossgate-community-collection/api/auth.php"
API_URL_REAUTH = "http://localhost/crossgate/crossgate-community-collection/api/reauth.php"
API_URL_DOWNLOAD = "http://localhost/crossgate/crossgate-community-collection/api/download.php" 
API_URL_COLLECTION = "http://localhost/crossgate/crossgate-community-collection/api/getcollection.php"
API_URL_GETBADGES = "http://localhost/crossgate/crossgate-community-collection/api/badges.php"
API_URL_UPDATEBADGES = "http://localhost/crossgate/crossgate-community-collection/api/updateBadges.php"
API_KEY = "3a0741edc4b7725d2473e2f9e887eba2.9310aef665af94f5e6f40ac3f71708ba03c6231c9705ac530dda434ab2b951b0"

GLOBAL_PORT = None
COLLECTION_RETURN = {}

def save_config(config_data):
    try:
        with CONFIG_FILE.open("w", encoding="utf-8") as f:
            json.dump(config_data, f, indent=4)
    except Exception as e:
        logging.error(f"Failed to save config.json: {e}")

def load_config():
    if not CONFIG_FILE.exists():
        return {}
    try:
        with CONFIG_FILE.open("r", encoding="utf-8") as f:
            return json.load(f)
    except json.JSONDecodeError:
        logging.error(
            "Corrupted config.json file. Falling back to empty configuration."
        )
        config = {}
        config["global_dir"] = str(BASE_DIR / "cgcc_installs")
        config["session_token"] = ""
        device_id = str(uuid.uuid4())
        config["device_id"] = device_id
        config["custom_dirs"] = {}
        config["minimize_to_tray"] = True
        save_config(config)
        return config

APP_CONFIG = load_config()
def get_device_id():
    config = APP_CONFIG
    device_id = config.get("device_id")
    if not device_id:
        device_id = str(uuid.uuid4())
        config["device_id"] = device_id
        save_config(config)

    return device_id

def save_token(token):
    if token:
        config = APP_CONFIG
        config["session_token"] = token
        save_config(config)
    else:
        logging.error("Empty/null session token provided.")

def load_token():
    config = APP_CONFIG
    return config.get("session_token")

def find_free_port():
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.bind(('', 0))
        return s.getsockname()[1]

def load_image_or_fallback(url, fallback_color=None, fallback_text=""):
    pixmap = QPixmap()
    if url:
        try:
            resp = requests.get(url, timeout=3)
            if resp.status_code == 200:
                pixmap.loadFromData(resp.content)
                return pixmap
        except requests.RequestException as e:
            logging.error(f"Image load failed for {url}: {e}")
            
    if fallback_color:
        pixmap = QPixmap(300, 150)
        pixmap.fill(QColor(fallback_color))
    return pixmap

# signaling to pyqt
class AppSignals(QObject):
    navigate_ui = pyqtSignal(str)
    unlock_badge_received = pyqtSignal(str, str) # badge_id, group_ref

signals = AppSignals()

# flask server
app = Flask(__name__)
@app.route("/get_command", methods=["GET"])
def get_command():
    cmd = request.args.get("cmd", "HOME")
    signals.navigate_ui.emit(cmd)
    return jsonify({"status": "running", "command": cmd})

@app.route("/get_user_info", methods=["GET"])
def get_user_info_endpoint():
    token = load_token()
    if not token:
        logging.warning("No active user session found")
        return jsonify({"status": "error", "message": "No active user session"}), 401

    headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
    payload = {
        "type": "user",
        "target": token,
        "address": get_device_id(),
        "os": platform.system(),
        "fetch_images": False
    }
    obtained_badges = {}
    profile_tags = "Unknown"
    username = "Unknown"
    try:
        # Fetch profile and owned badge data from API
        res = requests.post(API_URL_GETBADGES, json=payload, headers=headers, timeout=5)
        if res.status_code == 200:
            u_json = res.json()
            u_data = u_json.get("data", {})
            profile_tags = u_json.get("profileTags", "Unknown")
            username = u_json.get("username", profile_tags.split("#")[0] if "#" in profile_tags else profile_tags)

            # Map groupRef -> list of obtained badge IDs
            for g_ref, g_info in u_data.items():
                badge_list = g_info.get("badgeList", {})
                obtained_badges[g_ref] = list(badge_list.keys())
    except Exception as e:
        logging.error(f"Failed to fetch user profile info for local API: {e}")
        return jsonify({"status": "error", "message": "Failed to communicate with remote API"}), 500

    return jsonify({
        "status": "success",
        "profileTags": profile_tags,
        "username": username,
        "obtainedBadges": obtained_badges
    })

@app.route("/unlock_badge", methods=["POST"])
def unlock_badge_endpoint():
    data = request.json or {}
    badge_id = data.get("badge_id")
    group_ref = data.get("group_ref")
    
    if not badge_id or not group_ref:
        return jsonify({"status": "error", "message": "Missing badge_id or group_ref"}), 400
        
    signals.unlock_badge_received.emit(badge_id, group_ref)
    return jsonify({"status": "queued", "badge_id": badge_id})

def run_flask_server(port):
    serve(app, host='127.0.0.1', port=5000)


def set_dark(window):
    if hasattr(QGuiApplication, "styleHints"):
        QGuiApplication.styleHints().setColorScheme(Qt.ColorScheme.Dark)
    if platform.system() == "Windows":
        import ctypes
        hwnd = int(window.winId())
        DWMWA_USE_IMMERSIVE_DARK_MODE = 20
        value = ctypes.c_int(1)
        try:
            ctypes.windll.dwmapi.DwmSetWindowAttribute(
                hwnd,
                DWMWA_USE_IMMERSIVE_DARK_MODE,
                ctypes.byref(value),
                ctypes.sizeof(value),
            )
        except Exception:
            try:
                DWMWA_USE_IMMERSIVE_DARK_MODE_OLD = 19
                ctypes.windll.dwmapi.DwmSetWindowAttribute(
                    hwnd,
                    DWMWA_USE_IMMERSIVE_DARK_MODE_OLD,
                    ctypes.byref(value),
                    ctypes.sizeof(value),
                )
            except Exception:
                pass

# main code 
class StartupFlow(QMainWindow):
    # handle the login/session and clts fetching
    def __init__(self, main_app_reference):
        super().__init__()
        self.main_app = main_app_reference
        self.setWindowFlags(Qt.WindowType.FramelessWindowHint)
        self.resize(400, 500)
        self.setStyleSheet("background-color: #111213; color: white; font-family: 'Segoe UI';")
        
        self.layout = QVBoxLayout()
        self.layout.setAlignment(Qt.AlignmentFlag.AlignCenter)
        
        self.gif_label = QLabel()
        self.gif_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.movie = QMovie(str(BASE_DIR / "loading.gif"))
        if self.movie.isValid():
            self.gif_label.setMovie(self.movie)
            self.movie.start()
        else:
            self.gif_label.setText("[ LOADING... ]")
            self.gif_label.setStyleSheet("font-size: 24px; color: #5865f2; font-weight: bold;")
        self.layout.addWidget(self.gif_label)

        self.status_label = QLabel("Verifying Session...")
        self.status_label.setStyleSheet("font-size: 16px; font-weight: bold; margin-top: 20px;")
        self.status_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.layout.addWidget(self.status_label)
        
        container = QWidget()
        container.setLayout(self.layout)
        self.setCentralWidget(container)
        
        QTimer.singleShot(1000, self.verify_session)

    def fetch_collection(self, token, target_ids, marked_stats):
        self.status_label.setText("Fetching Library Data...")
        if not target_ids:
            logging.warning("User markout returned no valid collection keys.")
        
        # no img by deflt
        payload = {
            "tokens": token,
            "targetlibs": target_ids,
            "os": platform.system(),
            "address": get_device_id(),
            "fetch_images": False
        }
        headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
        
        try:
            response = requests.post(API_URL_COLLECTION, json=payload, headers=headers, timeout=10)
            if response.status_code == 200:
                try:
                    data = response.json()
                except Exception as e:
                    logging.error(f"JSON Decode Error. Server returned: {response.text}")
                    msgBox = QMessageBox(self)
                    msgBox.setWindowTitle("API Error")
                    msgBox.setText(
                        "Received invalid or empty data from the server."
                    )
                    msgBox.setIcon(QMessageBox.Icon.Warning)
                    msgBox.setStandardButtons(
                        QMessageBox.StandardButton.Ok
                    )
                    msgBox.setStyleSheet("""
                        QMessageBox {
                            background-color: #2b2d31;
                        }
                        QLabel {
                            background-color: #2b2d31;
                            color: #ffffff;
                            font-size: 14px;
                        }
                        QPushButton {
                            background-color: #383a40;
                            color: white;
                            border-radius: 4px;
                            padding: 6px 12px;
                            font-weight: bold;
                        }
                        QPushButton:hover {
                            background-color: #5865f2;
                        }
                    """)
                    msgBox.exec()
                    self.show_login_ui()
                    return
                    
                # Check what needs caching
                missing_images_ids = []
                for lib_id, app in data.items():
                    icon_file = app.get("libsIcon", "")
                    banner_file = app.get("libsBanners", "")
                    
                    icon_path = CACHE_DIR / f"{lib_id}_{icon_file}" if icon_file else None
                    banner_path = CACHE_DIR / f"{lib_id}_{banner_file}" if banner_file else None

                    stats = marked_stats.get(lib_id, {})
                    app["playtime"] = str(stats.get("Hours", 0))
                    app["last_login"] = stats.get("lastLog", "Never")
                    app["local_icon"] = str(icon_path) if icon_path and icon_path.exists() else None
                    app["local_banner"] = str(banner_path) if banner_path and banner_path.exists() else None

                    if not app["local_icon"] or not app["local_banner"]:
                        missing_images_ids.append(lib_id)
                
                if missing_images_ids:
                    self.status_label.setText("Downloading Cache Artwork...")
                    img_payload = payload.copy()
                    img_payload["targetlibs"] = missing_images_ids
                    img_payload["fetch_images"] = True 
                    
                    img_response = requests.post(API_URL_COLLECTION, json=img_payload, headers=headers, timeout=15)
                    if img_response.status_code == 200:
                        try:
                            img_data = img_response.json()
                            for lib_id, app_img in img_data.items():
                                icon_b64 = app_img.get("icon_base64")
                                banner_b64 = app_img.get("banner_base64")
                                icon_name = app_img.get("libsIcon", "icon.png")
                                banner_name = app_img.get("libsBanners", "banner.png")
                                
                                for old_f in CACHE_DIR.glob(f"{lib_id}_*"):
                                    old_f.unlink(missing_ok=True)
                                
                                if icon_b64:
                                    p = CACHE_DIR / f"{lib_id}_{icon_name}"
                                    p.write_bytes(base64.b64decode(icon_b64))
                                    data[lib_id]["local_icon"] = str(p)
                                    
                                if banner_b64:
                                    p = CACHE_DIR / f"{lib_id}_{banner_name}"
                                    p.write_bytes(base64.b64decode(banner_b64))
                                    data[lib_id]["local_banner"] = str(p)
                        except Exception:
                            logging.error("Failed to decode image JSON payload.")

                global COLLECTION_RETURN
                COLLECTION_RETURN = data
                self.main_app.initialize_main_ui()
                self.close()
            else:
                msgBox = QMessageBox(self)
                msgBox.setWindowTitle("Collection Error")
                msgBox.setText(
                    "Failed to load collection data."
                )
                msgBox.setIcon(QMessageBox.Icon.Warning)
                msgBox.setStandardButtons(
                    QMessageBox.StandardButton.Ok
                )
                msgBox.setStyleSheet("""
                    QMessageBox {
                        background-color: #2b2d31;
                    }
                    QLabel {
                        background-color: #2b2d31;
                        color: #ffffff;
                        font-size: 14px;
                    }
                    QPushButton {
                        background-color: #383a40;
                        color: white;
                        border-radius: 4px;
                        padding: 6px 12px;
                        font-weight: bold;
                    }
                    QPushButton:hover {
                        background-color: #5865f2;
                    }
                """)
                msgBox.exec()
                self.show_login_ui()
        except requests.ConnectionError:
            msgBox = QMessageBox(self)
            msgBox.setWindowTitle("Network Error")
            msgBox.setText(
                "Could not reach collection API."
            )
            msgBox.setIcon(QMessageBox.Icon.Warning)
            msgBox.setStandardButtons(
                QMessageBox.StandardButton.Ok
            )
            msgBox.setStyleSheet("""
                QMessageBox {
                    background-color: #2b2d31;
                }
                QLabel {
                    background-color: #2b2d31;
                    color: #ffffff;
                    font-size: 14px;
                }
                QPushButton {
                    background-color: #383a40;
                    color: white;
                    border-radius: 4px;
                    padding: 6px 12px;
                    font-weight: bold;
                }
                QPushButton:hover {
                    background-color: #5865f2;
                }
            """)
            msgBox.exec()
            self.show_login_ui()

    def parse_markout_data(self, response_data):
        try:
            markout_str = response_data.get("profileMarkOut", "{}")
            markout_data = json.loads(markout_str)
            marked_dict = markout_data.get("marked", {})
            return list(marked_dict.keys()), marked_dict
        except Exception as e:
            logging.error(f"Failed to parse profileMarkOut dynamically: {e}")
            return [], {}
        
    def save_profile_cache(self, data):
        PROFILE_CACHE["profileTags"] = data.get("profileTags", "unknown")
        PROFILE_CACHE["profileNames"] = data.get("profileNames", "User")
        PROFILE_CACHE["profileBios"] = data.get("profileBios", "")
        PROFILE_CACHE["profileJDates"] = data.get("profileJDates", "")
        PROFILE_CACHE["profileAttachs"] = data.get("profileAttachs", "")

    def verify_session(self):
        saved_token = load_token()
        if not saved_token:
            self.show_login_ui()
            return
            
        try:
            payload = {
                "tokens": saved_token,
                "address": get_device_id(),
                "os": platform.system()
            }
            headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
            
            response = requests.put(API_URL_REAUTH, json=payload, headers=headers, timeout=10)
            data = response.json()
            
            if data.get("message") == "Session Valid":
                self.save_profile_cache(data)
                target_ids, marked_stats = self.parse_markout_data(data)
                self.fetch_collection(saved_token, target_ids, marked_stats)
            else:
                self.show_login_ui()
                
        except requests.ConnectionError:
            self.status_label.setText("API Unreachable. Please check connection.")
            logging.error("Reauth API Unreachable.")
            QTimer.singleShot(3000, self.show_login_ui)

    def show_login_ui(self):
        for i in reversed(range(self.layout.count())): 
            widget = self.layout.itemAt(i).widget()
            if widget:
                widget.setParent(None)

        self.layout.addWidget(QLabel("<h2 style='text-align:center;'>LOGIN REQUIRED</h2>"))
        
        self.user_input = QLineEdit()
        self.user_input.setPlaceholderText("Account Name")
        self.user_input.setStyleSheet("padding: 10px; background: #1e1f22; border-radius: 4px;")
        
        self.pass_input = QLineEdit()
        self.pass_input.setPlaceholderText("Password")
        self.pass_input.setEchoMode(QLineEdit.EchoMode.Password)
        self.pass_input.setStyleSheet("padding: 10px; background: #1e1f22; border-radius: 4px;")
        
        self.login_btn = QPushButton("Sign In")
        self.login_btn.setStyleSheet("background-color: #2979ff; padding: 10px; border-radius: 4px; font-weight: bold;")
        self.login_btn.clicked.connect(self.process_login)
        
        self.layout.addWidget(self.user_input)
        self.layout.addWidget(self.pass_input)
        self.layout.addWidget(self.login_btn)

    def process_login(self):
        self.login_btn.setText("Signing in...")
        payload = {
            "username": self.user_input.text(),
            "password": self.pass_input.text(),
            "os": platform.system(),
            "address": get_device_id(),
            # "sessionless": not self.stay_signed_in.isChecked()
        }
        headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
        
        try:
            response = requests.post(API_URL_AUTH, json=payload, headers=headers, timeout=10)
            data = response.json()
            
            if data.get("message") == "Login Successful":
                token = data.get("sessionToken")
                if token:
                    save_token(token)

                self.save_profile_cache(data)
                target_ids, marked_stats = self.parse_markout_data(data)
                self.fetch_collection(token, target_ids, marked_stats)
            else:
                self.login_btn.setText("Sign In")
                msgBox = QMessageBox(self)
                msgBox.setWindowTitle("Login Failed")
                msgBox.setText(
                    "Invalid Credentials"
                )
                msgBox.setIcon(QMessageBox.Icon.Critical)
                msgBox.setStandardButtons(
                    QMessageBox.StandardButton.Ok
                )
                msgBox.setStyleSheet("""
                    QMessageBox {
                        background-color: #2b2d31;
                    }
                    QLabel {
                        background-color: #2b2d31;
                        color: #ffffff;
                        font-size: 14px;
                    }
                    QPushButton {
                        background-color: #383a40;
                        color: white;
                        border-radius: 4px;
                        padding: 6px 12px;
                        font-weight: bold;
                    }
                    QPushButton:hover {
                        background-color: #5865f2;
                    }
                """)
                msgBox.exec()
        except requests.ConnectionError:
            self.login_btn.setText("Sign In")
            msgBox = QMessageBox(self)
            msgBox.setWindowTitle("Connection Error")
            msgBox.setText(
                "Cannot connect to the Web API"
            )
            msgBox.setIcon(QMessageBox.Icon.Critical)
            msgBox.setStandardButtons(
                QMessageBox.StandardButton.Ok
            )
            msgBox.setStyleSheet("""
                QMessageBox {
                    background-color: #2b2d31;
                }
                QLabel {
                    background-color: #2b2d31;
                    color: #ffffff;
                    font-size: 14px;
                }
                QPushButton {
                    background-color: #383a40;
                    color: white;
                    border-radius: 4px;
                    padding: 6px 12px;
                    font-weight: bold;
                }
                QPushButton:hover {
                    background-color: #5865f2;
                }
            """)
            msgBox.exec()

class DownloadWorker(QThread):
    progress = pyqtSignal(int)
    status_update = pyqtSignal(str)
    finished = pyqtSignal(bool, str)

    def __init__(self, lib_id, target_dir, fdr_libs, token, version, is_update=False, old_folder="", dl_type="fdrLibs"):
        super().__init__()
        self.lib_id = lib_id
        self.target_dir = target_dir
        self.fdr_libs = fdr_libs
        self.token = token
        self.version = version
        self.is_update = is_update
        self.old_folder = old_folder
        self.dl_type = dl_type

    def run(self):
        self.status_update.emit("Starting download...")
        headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
        payload = {
            "tokens": self.token,
            "libsIds": self.lib_id,
            "isRollback": (self.dl_type == "rollbacks"),
            "ver": self.version,
            "os": platform.system(),
            "address": get_device_id()
        }
        
        try:
            os.makedirs(self.target_dir, exist_ok=True)
            zip_path = os.path.join(self.target_dir, f"{self.lib_id}_temp.zip")

            with requests.post(API_URL_DOWNLOAD, headers=headers, json=payload, stream=True, timeout=15) as r:
                r.raise_for_status()
                total_length = int(r.headers.get('Content-Length', 0))
                
                with open(zip_path, 'wb') as f:
                    downloaded = 0
                    for chunk in r.iter_content(chunk_size=8192):
                        if chunk:
                            f.write(chunk)
                            downloaded += len(chunk)
                            if total_length > 0:
                                calc_prog = int((downloaded / total_length) * 100)
                                self.progress.emit(calc_prog)

            self.status_update.emit("Extracting package...")
            folder_name = self.fdr_libs.replace('.zip', '') if self.fdr_libs else f"{self.lib_id}_app"
            extract_path = os.path.join(self.target_dir, folder_name)
            os.makedirs(extract_path, exist_ok=True)
            with zipfile.ZipFile(zip_path, 'r') as zip_ref:
                zip_ref.extractall(extract_path)
            
            os.remove(zip_path)
            
            if self.is_update and self.old_folder:
                old_path = os.path.join(self.target_dir, self.old_folder)
                if os.path.exists(old_path) and old_path != extract_path:
                    shutil.rmtree(old_path)
            
            self.finished.emit(True, "Installation Complete")

        except Exception as e:
            logging.error(f"Download error: {e}")
            self.finished.emit(False, str(e))

# for the badges display
class SquareCoverLabel(QWidget):
    def __init__(self, size=58, parent=None):
        super().__init__(parent)
        self.setFixedSize(size, size)
        self.pixmap = None

    def set_pixmap(self, pixmap):
        self.pixmap = pixmap
        self.update()

    def paintEvent(self, event):
        super().paintEvent(event)
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        painter.setRenderHint(QPainter.RenderHint.SmoothPixmapTransform)
        
        rect = self.rect()
        if not self.pixmap or self.pixmap.isNull():
            painter.fillRect(rect, QColor("#1e1f22"))
            return

        # Scaled to fill entire box, cropping overhangs (object-fit: cover)
        scaled = self.pixmap.scaled(
            rect.size(),
            Qt.AspectRatioMode.KeepAspectRatioByExpanding,
            Qt.TransformationMode.SmoothTransformation
        )
        x = (scaled.width() - rect.width()) // 2
        y = (scaled.height() - rect.height()) // 2
        painter.drawPixmap(0, 0, scaled, x, y, rect.width(), rect.height())

class BadgeToast(QWidget):
    def __init__(self, badge_name, badge_desc="", icon_pixmap=None):
        super().__init__()
        self.setWindowFlags(
            Qt.WindowType.FramelessWindowHint |
            Qt.WindowType.WindowStaysOnTopHint |
            Qt.WindowType.Tool |
            Qt.WindowType.WindowDoesNotAcceptFocus
        )
        self.setAttribute(Qt.WidgetAttribute.WA_TranslucentBackground, True)
        self.setFixedSize(340, 80)
        container = QFrame(self)
        container.setGeometry(0, 0, 340, 80)
        container.setStyleSheet("""
            QFrame {
                background-color: #1e1f22;
                border-radius: 8px;
            }""")
        layout = QHBoxLayout(container)
        layout.setContentsMargins(12, 10, 12, 10)
        layout.setSpacing(12)

        # Icon
        self.icon_widget = SquareCoverLabel(58)
        if icon_pixmap:
            self.icon_widget.set_pixmap(icon_pixmap)
        layout.addWidget(self.icon_widget)

        # Text
        text_layout = QVBoxLayout()
        text_layout.setSpacing(2)
        text_layout.setAlignment(Qt.AlignmentFlag.AlignVCenter)
        lbl_header = QLabel("OBTAINED BADGE")
        lbl_header.setStyleSheet("color: #5865f2; font-size: 10px; font-weight: bold; background: transparent;")
        lbl_name = QLabel(badge_name)
        lbl_name.setStyleSheet("color: #ffffff; font-size: 13px; font-weight: bold; background: transparent;")
        lbl_desc = QLabel(badge_desc)
        lbl_desc.setStyleSheet("color: #b5bac1; font-size: 11px; background: transparent;")
        lbl_desc.setWordWrap(True)

        text_layout.addWidget(lbl_header)
        text_layout.addWidget(lbl_name)
        if badge_desc:
            text_layout.addWidget(lbl_desc)
            
        layout.addLayout(text_layout)
        self.position_bottom_left()

        # Auto-dismiss after 7 seconds
        self.dismiss_timer = QTimer(self)
        self.dismiss_timer.setSingleShot(True)
        self.dismiss_timer.timeout.connect(self.close)
        self.dismiss_timer.start(7000)

    def position_bottom_left(self):
        screen = QApplication.primaryScreen().availableGeometry()
        margin = 25
        x = screen.left() + margin
        y = screen.bottom() - self.height() - margin
        self.move(x, y)

# the actual interface
# Custom QFrame for the background image on the home page
class CoverFrame(QFrame):
    def __init__(self, parent=None):
        super().__init__(parent)
        self.pixmap = None

    def set_image(self, image_path):
        if image_path and os.path.exists(image_path):
            self.pixmap = QPixmap(image_path)
        else:
            self.pixmap = None
        self.update()

    def paintEvent(self, event):
        super().paintEvent(event)
        if not self.pixmap or self.pixmap.isNull():
            painter = QPainter(self)
            painter.fillRect(self.rect(), QColor("#111213"))
            return
            
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        painter.setRenderHint(QPainter.RenderHint.SmoothPixmapTransform)
        window_size = self.size()
        scaled_pixmap = self.pixmap.scaled(
            window_size, 
            Qt.AspectRatioMode.KeepAspectRatioByExpanding, 
            Qt.TransformationMode.SmoothTransformation
        )
        x_offset = (scaled_pixmap.width() - window_size.width()) // 2
        y_offset = (scaled_pixmap.height() - window_size.height()) // 2
        painter.drawPixmap(0, 0, scaled_pixmap, x_offset, y_offset, window_size.width(), window_size.height())

# mostly front-end
class LauncherApp(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowIcon(QIcon('asset/cgcclogotrsp.ico'))
        self.setWindowTitle("CGCC Launcher")
        self.resize(1366, 768)
        set_dark(self)
        self.setStyleSheet("""
            QMainWindow { background-color: #111213; }
            QWidget { color: #e3e3e3; font-family: 'Segoe UI', sans-serif; }
            QLineEdit { background-color: #1e1f22; border: 1px solid #2b2d31; border-radius: 4px; padding: 6px; color: white; }
            QListWidget { background-color: transparent; border: none; }
        """)
        self.running_processes = {}
        self.process_timers = {}
        self.active_app_data = None
        self.setWindowFlags(self.windowFlags() | Qt.WindowType.WindowMinimizeButtonHint | Qt.WindowType.WindowCloseButtonHint)
        signals.unlock_badge_received.connect(self.process_badge_unlock)

    def build_ui(self):
        main_layout = QHBoxLayout()
        main_layout.setContentsMargins(0, 0, 0, 0)
        main_layout.setSpacing(0)

        # Left Icon Bar
        self.icon_bar = QWidget()
        self.icon_bar.setFixedWidth(65)
        self.icon_bar.setStyleSheet("background-color: #1e1f22; border-right: 1px solid #2b2d31;")
        icon_layout = QVBoxLayout(self.icon_bar)
        icon_layout.setContentsMargins(5, 15, 5, 15)
        icon_layout.setAlignment(Qt.AlignmentFlag.AlignTop)

        # listed collection as icons
        for lib_id, app_data in COLLECTION_RETURN.items():
            btn = QPushButton()
            btn.setFixedSize(55, 55)
            local_icon = app_data.get("local_icon")
            icon_text = app_data.get("libsTitles", lib_id)[:3].upper()
            if local_icon and os.path.exists(local_icon):
                icon_path_qt = local_icon.replace('\\', '/')
                btn.setStyleSheet(f"""
                    QPushButton {{
                        border-radius: 8px;
                        border-image: url({icon_path_qt}) 0 0 0 0 stretch stretch;
                    }}
                    QPushButton:hover {{ border: 2px solid #5865f2; }}
                """)
            else:
                btn.setText(icon_text)
                btn.setStyleSheet("background-color: #4f83d6; color: white; border-radius: 8px; font-weight: bold;")
                
            btn.clicked.connect(lambda checked, data=app_data: self.switch_to_home_app(data))
            icon_layout.addWidget(btn)

        # Page Stack Initialization
        self.page_stack = QStackedWidget()
        self.init_home_page()
        self.init_detail_page()
        self.init_library_page()
        self.init_downloads_page()
        self.init_settings_page() 
        self.page_stack.setCurrentIndex(0)
        if COLLECTION_RETURN:
            first_clts = list(COLLECTION_RETURN.keys())[0]
            self.switch_to_home_app(COLLECTION_RETURN[first_clts])
        self.heartbeat()
        self.check_first_use()
        
        # Options Bar
        optionsList = [
            {"id": "1", "title": "Library", "icon_img": "asset/lbr.svg", "icon_text": "LBR", "target_idx": 2},
            {"id": "2", "title": "Downloads", "icon_img": "asset/dl.svg", "icon_text": "DL", "target_idx": 3},
            {"id": "3", "title": "Settings", "icon_img": "asset/stg.svg", "icon_text": "STG", "target_idx": 4},
        ]
        
        self.options_bar = QWidget()
        self.options_bar.setFixedWidth(40)
        self.options_bar.setStyleSheet("background-color: #1e1f22; border-left: 1px solid #1e1f22;")
        options_layout = QVBoxLayout(self.options_bar)
        options_layout.setContentsMargins(5, 15, 5, 15)
        options_layout.setSpacing(10)
        options_layout.setAlignment(Qt.AlignmentFlag.AlignTop)

        for opt_data in optionsList:
            btn = QPushButton("")
            btn.setFixedSize(32, 32)
            btn.setStyleSheet(f"""
                QPushButton {{
                    color: #b5bac1; font-weight: bold; border-radius: 8px; font-size: 14px; border: 2px solid transparent;
                    border-image: url({opt_data['icon_img']}) 0 0 0 0 stretch stretch;
                }}
                QPushButton:hover {{ border: 2px solid #4e5158; background-color: #4e5158; color: purple; }}
            """)
            btn.clicked.connect(lambda checked, idx=opt_data["target_idx"]: self.page_stack.setCurrentIndex(idx))
            options_layout.addWidget(btn)

        main_layout.addWidget(self.icon_bar)
        main_layout.addWidget(self.page_stack)
        main_layout.addWidget(self.options_bar)

        central_widget = QWidget()
        central_widget.setLayout(main_layout)
        self.setCentralWidget(central_widget)

    def get_app_install_state(self, app_data):
        # detemine the state of the software(either can launch or there's update or not)
        lib_id = app_data.get("libsIds")
        install_path = APP_CONFIG["custom_dirs"].get(lib_id, os.path.join(APP_CONFIG["global_dir"], lib_id))
        
        fdr_libs = app_data.get("fdrLibs", "")
        rollbacks = app_data.get("rollbacks", "")
        file_details_str = app_data.get("detailData", "{}")
        
        try:
            file_details = json.loads(file_details_str) if file_details_str else {}
        except json.JSONDecodeError:
            file_details = {}

        curr_folder = fdr_libs.replace(".zip", "") if fdr_libs else ""
        prev_folder = rollbacks.replace(".zip", "") if rollbacks else ""
        
        curr_exe = file_details.get("fdrLibs", {}).get("executables", "")
        prev_exe = file_details.get("rollbacks", {}).get("executables", "")
        
        curr_installed = os.path.exists(os.path.join(install_path, curr_folder, curr_exe)) if curr_folder and curr_exe else False
        prev_installed = os.path.exists(os.path.join(install_path, prev_folder, prev_exe)) if prev_folder and prev_exe else False
        
        if curr_installed: return "LAUNCH"
        if prev_installed: return "UPDATE"
        return "DOWNLOAD"

    def process_badge_unlock(self, badge_id, group_ref):
        token = load_token()
        if not token:
            return

        payload = {
            "tokens": token,
            "target": badge_id,
            "groupref": group_ref,
            "address": get_device_id(),
            "os": platform.system()
        }
        headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
        try:
            response = requests.post(API_URL_UPDATEBADGES, json=payload, headers=headers, timeout=10)
            res_data = response.json()
            if response.status_code == 200 and "Successfully" in res_data.get("message", ""):
                # Check local badge cache for display details
                icon_path = CACHE_DIR / group_ref / f"{badge_id}.png"
                pixmap = QPixmap(str(icon_path)) if icon_path.exists() else None
                # Show toast notification
                self.toast = BadgeToast(
                    badge_name=f"Badge #{badge_id}", 
                    badge_desc="New Badges Unlocked!", 
                    icon_pixmap=pixmap
                )
                self.toast.show()
                
                # Refresh details UI if active
                if self.active_app_data and hasattr(self, 'render_badges_section'):
                    self.render_badges_section(self.active_app_data.get("libsIds"))
        except Exception as e:
            logging.error(f"Failed unlocking the badge: {e}")

    def check_first_use(self):
        # tldr: first time use badge check and awarding
        token = load_token()
        if not token:
            return

        headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
        user_payload = {
            "type": "user",
            "target": token,
            "address": get_device_id(),
            "os": platform.system(),
            "fetch_images": True
        }
        try:
            res_user = requests.post(API_URL_GETBADGES, json=user_payload, headers=headers, timeout=10)
            if res_user.status_code != 200:
                return

            u_json = res_user.json()
            u_data = u_json.get("data", {})
            has_first_time_badge = False
            target_group_ref = "CGCC"  # Default fallback group_ref
            for g_ref, g_info in u_data.items():
                badge_list = g_info.get("badgeList", {})
                if "1" in badge_list:
                    has_first_time_badge = True
                    break
                if not target_group_ref and g_ref:
                    target_group_ref = g_ref

            if not has_first_time_badge:
                update_payload = {
                    "tokens": token,
                    "target": "1",
                    "groupref": target_group_ref,
                    "address": get_device_id(),
                    "os": platform.system()
                }
                
                response = requests.post(API_URL_UPDATEBADGES, json=update_payload, headers=headers, timeout=10)
                if response.status_code == 200:
                    icon_path = CACHE_DIR / target_group_ref / "logo-github.svg"
                    pixmap = QPixmap(str(icon_path)) if icon_path.exists() else None

                    self.first_time_toast = BadgeToast(
                        badge_name="Welcome to the Cult :)",
                        badge_desc="Congratulations on your first badges",
                        icon_pixmap=pixmap
                    )
                    self.first_time_toast.show()

        except Exception as e:
            logging.error(f"Error handling fu badge check: {e}")

    def init_home_page(self):
            if COLLECTION_RETURN:
                first_clts_id = list(COLLECTION_RETURN.keys())[0]
                first_clt_data = COLLECTION_RETURN.get(first_clts_id, "{}")
                detail_data_str = first_clt_data.get("detailData", "{}")
                if isinstance(detail_data_str, str):
                    try:
                        detail_data = json.loads(detail_data_str) if detail_data_str else {}
                    except json.JSONDecodeError:
                        detail_data = {}
                else:
                    detail_data = detail_data_str
                    
                theme = detail_data.get("theme", "dark")
                text_color = "white" if theme != "dark" else "black"
                
                self.home_page = CoverFrame() 
                layout = QVBoxLayout(self.home_page)
                layout.setContentsMargins(40, 40, 40, 40)
                
                self.home_title = QLabel("libsTitles, Untitled")
                self.home_title.setStyleSheet(f"font-size: 48px; font-weight: bold; color: {text_color}; background-color: transparent;")
                layout.addWidget(self.home_title)
    
                self.home_desc = QLabel("libsDesc, No description available.")
                self.home_desc.setWordWrap(True)
                self.home_desc.setFixedWidth(500)
                self.home_desc.setStyleSheet(f"font-size: 16px; color: {text_color}; margin-top: 10px; background-color: transparent;")
                layout.addWidget(self.home_desc)
                layout.addStretch() 
                
                launch_layout = QHBoxLayout()
                launch_layout.setSpacing(10)
    
                self.home_btn_action = QPushButton("LAUNCH")
                self.home_btn_action.setFixedSize(220, 60)
                self.home_btn_action.clicked.connect(self.handle_app_action)
                launch_layout.addWidget(self.home_btn_action)
    
                # Square Side Update Button for Home Page
                self.home_btn_update = QPushButton("⟳")
                self.home_btn_update.setFixedSize(60, 60)
                self.home_btn_update.setToolTip("Update Available")
                self.home_btn_update.setStyleSheet("""
                    QToolTip { background-color: black; color: white; border: black solid 1px;}
                    QPushButton { background-color: #f39c12; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; }
                    QPushButton:hover { background-color: #f39c12; }
                """)
                self.home_btn_update.clicked.connect(lambda: self.handle_app_action(forced_action="UPDATE"))
                self.home_btn_update.hide()
                launch_layout.addWidget(self.home_btn_update)
                
                launch_layout.addStretch()
                layout.addLayout(launch_layout)
            else:
                self.home_page = CoverFrame() 
                layout = QVBoxLayout(self.home_page)
                layout.setContentsMargins(40, 40, 40, 40)
                
                self.home_title = QLabel("No Collection")
                self.home_title.setStyleSheet("font-size: 48px; color: #d5d6d9; font-weight: bold; background-color: transparent;")
                layout.addWidget(self.home_title)
    
                self.home_desc = QLabel("Add collection to your MarkOut library first to try them out")
                self.home_desc.setWordWrap(True)
                self.home_desc.setFixedWidth(500)
                self.home_desc.setStyleSheet("font-size: 16px; color: #e3e3e3; margin-top: 10px; background-color: transparent;")
                layout.addWidget(self.home_desc)
                layout.addStretch() 
                
                launch_layout = QHBoxLayout()
                self.home_btn_action = QPushButton("Browse for Collection")
                self.home_btn_action.setFixedSize(220, 60)
                self.home_btn_action.clicked.connect(self.handle_app_action)
                launch_layout.addWidget(self.home_btn_action)
                launch_layout.addStretch()
                layout.addLayout(launch_layout)
            
            self.page_stack.addWidget(self.home_page)
    
    def check_process_status(self, lib_id):
        proc = self.running_processes.get(lib_id)
        if proc and proc.poll() is not None:
            # Clean up trackers
            del self.running_processes[lib_id]
            self.process_timers[lib_id].stop()
            del self.process_timers[lib_id]
            
            # Revert BOTH buttons back to LAUNCH if the user is still on this app's page
            if self.active_app_data and self.active_app_data.get("libsIds") == lib_id:
                style = "QPushButton { background-color: #5865f2; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #4752c4; }"
                self.btn_action.setText("LAUNCH")
                self.btn_action.setStyleSheet(style)
                self.home_btn_action.setText("LAUNCH")
                self.home_btn_action.setStyleSheet(style)

    def init_detail_page(self):
            self.detail_page = QWidget()
            layout = QVBoxLayout(self.detail_page)
            layout.setContentsMargins(0, 0, 0, 0) 
            layout.setSpacing(0)
            self.detail_banner = CoverFrame()
            self.detail_banner.setFixedHeight(220)
            banner_layout = QVBoxLayout(self.detail_banner)
            banner_layout.setContentsMargins(40, 20, 40, 20)
                    
            btn_back = QPushButton("← Back to Library")
            btn_back.setFixedSize(150, 35)
            btn_back.setStyleSheet("background-color: rgba(43, 45, 49, 0.8); color: white; border-radius: 4px; font-weight: bold;")
            btn_back.clicked.connect(lambda: self.page_stack.setCurrentIndex(2))
            banner_layout.addWidget(btn_back, alignment=Qt.AlignmentFlag.AlignTop | Qt.AlignmentFlag.AlignLeft)
            banner_layout.addStretch()
            
            self.detail_title = QLabel("Game Settings")
            self.detail_title.setStyleSheet("font-size: 32px; font-weight: bold; color: white; background-color: transparent;")
            banner_layout.addWidget(self.detail_title)
            
            layout.addWidget(self.detail_banner)
            
            # Main Scroll Wrapper for Action Bar + Badges
            scroll_area = QScrollArea()
            scroll_area.setWidgetResizable(True)
            scroll_area.setStyleSheet("QScrollArea { border: none; background-color: transparent; }")
            
            scroll_content = QWidget()
            scroll_content.setStyleSheet("background-color: transparent;")
            content_layout = QVBoxLayout(scroll_content)
            content_layout.setContentsMargins(40, 20, 40, 40)
            content_layout.setSpacing(20)
    
            # Action Bar
            self.action_bar = QFrame()
            self.action_bar.setStyleSheet("background-color: #1e1f22; border-radius: 8px;")
            bar_layout = QHBoxLayout(self.action_bar)
            bar_layout.setContentsMargins(20, 15, 20, 15)
            bar_layout.setSpacing(10)
    
            self.btn_action = QPushButton("DOWNLOAD")
            self.btn_action.setFixedSize(180, 45)
            self.btn_action.clicked.connect(self.handle_app_action)
            bar_layout.addWidget(self.btn_action)
            
            # Update Button
            self.btn_update = QPushButton("⟳")
            self.btn_update.setFixedSize(45, 45)
            self.btn_update.setToolTip("Update Available")
            self.btn_update.setStyleSheet("""
                QToolTip { background-color: black; color: white; border: black solid 1px;}
                QPushButton { background-color: #f39c12; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; }
                QPushButton:hover { background-color: #f39c12; }
            """)
            self.btn_update.clicked.connect(lambda: self.handle_app_action(forced_action="UPDATE"))
            self.btn_update.hide()
            bar_layout.addWidget(self.btn_update)
    
            # Stats block inside the bar
            self.lbl_playtime = QLabel("PLAY TIME\n0 Hours")
            self.lbl_playtime.setStyleSheet("font-size: 13px; color: #b5bac1; font-weight: bold;")
            bar_layout.addWidget(self.lbl_playtime)
            bar_layout.addSpacing(20)
            
            self.lbl_lastlogin = QLabel("LAST LOGIN\nNever")
            self.lbl_lastlogin.setStyleSheet("font-size: 13px; color: #b5bac1; font-weight: bold;")
            bar_layout.addWidget(self.lbl_lastlogin)
            bar_layout.addSpacing(20)
            
            self.lbl_version = QLabel("VERSION\nUnknown")
            self.lbl_version.setStyleSheet("font-size: 13px; color: #b5bac1; font-weight: bold;")
            bar_layout.addWidget(self.lbl_version)
            bar_layout.addStretch()
    
            # Options Menu
            self.btn_options = QPushButton("")
            self.btn_options.setFixedSize(45, 45)
            self.btn_options.setStyleSheet("""
                QPushButton {
                    font-weight: bold; border-radius: 8px; font-size: 14px; border: 2px solid transparent;
                    border-image: url("asset/stg.svg") 0 0 0 0 stretch stretch;
                }
                QPushButton:hover { border: 2px solid #4e5158; background-color: #4e5158; color: white; }
            """)
            
            self.options_menu = QMenu(self.btn_options)
            self.options_menu.setStyleSheet("""
                QMenu { background-color: #2b2d31; color: white; border-radius: 4px; padding: 5px; }
                QMenu::item { padding: 8px 20px; border-radius: 4px; }
                QMenu::item:selected { background-color: #5865f2; }
            """)
            
            action_custom_dir = QAction("Change Install Directory", self.detail_page)
            action_custom_dir.triggered.connect(self.set_custom_app_dir)
            self.action_rollback = QAction("Rollback to Previous Version", self.detail_page)
            self.action_rollback.triggered.connect(self.rollback_app)
            self.action_uninstall = QAction("Uninstall Software", self.detail_page)
            self.action_uninstall.triggered.connect(self.uninstall_app)
            
            self.options_menu.addAction(action_custom_dir)
            self.options_menu.addAction(self.action_rollback)
            self.options_menu.addAction(self.action_uninstall)
            self.btn_options.setMenu(self.options_menu)
            
            bar_layout.addWidget(self.btn_options)
            content_layout.addWidget(self.action_bar)
            
            # Badges displayer
            self.badges_container = QWidget()
            self.badges_container.setStyleSheet("background-color: transparent;")
            self.badges_layout = QVBoxLayout(self.badges_container)
            self.badges_layout.setContentsMargins(0, 0, 0, 0)
            self.badges_layout.setSpacing(20)
            content_layout.addWidget(self.badges_container)
            
            scroll_area.setWidget(scroll_content)
            layout.addWidget(scroll_area)
            self.page_stack.addWidget(self.detail_page)
    
    def fetch_badges_data(self, lib_id):
        token = load_token()
        headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
        user_badge_ids = set()
        clts_groups = {}

        # fetching Owned Badges
        if token:
            try:
                user_payload = {
                    "type": "user",
                    "target": token,
                    "address": get_device_id(),
                    "os": platform.system(),
                    "fetch_images": True
                }
                res_user = requests.post(API_URL_GETBADGES, json=user_payload, headers=headers, timeout=10)
                if res_user.status_code == 200:
                    u_json = res_user.json()
                    u_data = u_json.get("data", {})
                    u_img = u_json.get("img", {})
                    
                    for g_ref, g_info in u_data.items():
                        g_dir = CACHE_DIR / g_ref
                        g_dir.mkdir(parents=True, exist_ok=True)
                        b_list = g_info.get("badgeList", {})
                        for b_id, b_val in b_list.items():
                            user_badge_ids.add(str(b_id))
                            b_icon = b_val.get("badgeIcon")
                            if b_icon and b_icon in u_img:
                                (g_dir / b_icon).write_bytes(base64.b64decode(u_img[b_icon]))
            except Exception as e:
                logging.error(f"Error fetching user badges: {e}")

        # Retrieving Collection Badges
        try:
            clts_payload = {
                "type": "clts",
                "target": lib_id,
                "fetch_images": True
            }
            res_clts = requests.post(API_URL_GETBADGES, json=clts_payload, headers=headers, timeout=10)
            if res_clts.status_code == 200:
                c_json = res_clts.json()
                c_data = c_json.get("data", {})
                c_img = c_json.get("img", {})
                
                for g_ref, g_info in c_data.items():
                    g_dir = CACHE_DIR / g_ref
                    g_dir.mkdir(parents=True, exist_ok=True)
                    
                    g_icon = g_info.get("icons")
                    if g_icon and g_icon in c_img:
                        (g_dir / g_icon).write_bytes(base64.b64decode(c_img[g_icon]))
                        
                    b_list = g_info.get("badgeList", {})
                    for b_id, b_val in b_list.items():
                        b_icon = b_val.get("badgeIcon")
                        if b_icon and b_icon in c_img:
                            (g_dir / b_icon).write_bytes(base64.b64decode(c_img[b_icon]))
                            
                clts_groups = c_data
        except Exception as e:
            logging.error(f"Error fetching collection badges: {e}")

        return user_badge_ids, clts_groups

    def render_badges_section(self, lib_id):
        # Clear previous badge widgets
        while self.badges_layout.count():
            child = self.badges_layout.takeAt(0)
            if child.widget():
                child.widget().deleteLater()

        user_badge_ids, clts_groups = self.fetch_badges_data(lib_id)

        if not clts_groups:
            no_badges = QLabel("No achievements or badges listed for this collection.")
            no_badges.setStyleSheet("color: #72767d; font-size: 14px; font-style: italic;")
            self.badges_layout.addWidget(no_badges)
            return

        # Render the Badges Group
        for g_ref, g_info in clts_groups.items():
            group_box = QFrame()
            group_box.setStyleSheet("background-color: #1e1f22; border-radius: 8px;")
            gb_layout = QVBoxLayout(group_box)
            gb_layout.setContentsMargins(20, 15, 20, 15)
            
            # Header with Group Title & Completion Stats
            header_layout = QHBoxLayout()
            title_text = g_info.get("badgeGroupTitle", "Badges Group")
            desc_text = g_info.get("badgeGroupDesc", "")
            
            title_lbl = QLabel(f"<b>{title_text}</b>" + (f" <span style='color:#b5bac1; font-size:12px;'>({desc_text})</span>" if desc_text else ""))
            title_lbl.setStyleSheet("font-size: 18px; color: white;")
            header_layout.addWidget(title_lbl)
            
            badge_list = g_info.get("badgeList", {})
            total_count = len(badge_list)
            obtained_count = sum(1 for b_id in badge_list if str(b_id) in user_badge_ids)
            percentage = int((obtained_count / total_count) * 100) if total_count > 0 else 0
            
            stats_lbl = QLabel(f"Unlocked {obtained_count}/{total_count} ({percentage}%)")
            stats_lbl.setStyleSheet("font-size: 14px; color: #5865f2; font-weight: bold;")
            header_layout.addWidget(stats_lbl, alignment=Qt.AlignmentFlag.AlignRight)
            gb_layout.addLayout(header_layout)

            # Percentage Progress Bar
            pbar = QProgressBar()
            pbar.setValue(percentage)
            pbar.setFixedHeight(6)
            pbar.setTextVisible(False)
            pbar.setStyleSheet("""
                QProgressBar {
                    background-color: #2b2d31; border-radius: 3px; border: none;
                }
                QProgressBar::chunk {
                    background-color: #5865f2; border-radius: 3px;
                }
            """)
            gb_layout.addWidget(pbar)
            gb_layout.addSpacing(10)

            # Grid for Individual Badges
            badges_grid = QGridLayout()
            badges_grid.setSpacing(12)
            row, col = 0, 0
            max_cols = 2

            for b_id, b_val in badge_list.items():
                card = QFrame()
                card.setFixedHeight(75)
                is_obtained = str(b_id) in user_badge_ids
                
                if is_obtained:
                    card.setStyleSheet("background-color: #2b2d31; border-radius: 6px;")
                else:
                    card.setStyleSheet("background-color: #18191c; border-radius: 6px;")

                card_layout = QHBoxLayout(card)
                card_layout.setContentsMargins(10, 10, 10, 10)

                # Badge Icon from Cache
                icon_lbl = QLabel()
                icon_lbl.setFixedSize(50, 50)
                icon_lbl.setScaledContents(True)

                b_icon = b_val.get("badgeIcon", "")
                cached_icon_path = CACHE_DIR / g_ref / b_icon if b_icon else None
                
                if cached_icon_path and cached_icon_path.exists():
                    pixmap = QPixmap(str(cached_icon_path))
                else:
                    pixmap = QPixmap(50, 50)
                    pixmap.fill(QColor("#2b2d31"))

                icon_lbl.setPixmap(pixmap)
                card_layout.addWidget(icon_lbl)

                # Badge Details
                info_layout = QVBoxLayout()
                info_layout.setSpacing(2)
                
                name_lbl = QLabel(b_val.get("badgeName", "Badge"))
                name_lbl.setStyleSheet("font-size: 14px; font-weight: bold; color: white;")
                desc_lbl = QLabel(b_val.get("badgeDesc", ""))
                desc_lbl.setWordWrap(True)
                desc_lbl.setStyleSheet("font-size: 12px; color: #b5bac1;")

                info_layout.addWidget(name_lbl)
                info_layout.addWidget(desc_lbl)
                info_layout.addStretch()
                card_layout.addLayout(info_layout)

                # Apply Grayed-Out Opacity Effect if Unobtained
                if not is_obtained:
                    opacity_effect = QGraphicsOpacityEffect(card)
                    opacity_effect.setOpacity(0.35)
                    card.setGraphicsEffect(opacity_effect)

                badges_grid.addWidget(card, row, col)
                col += 1
                if col >= max_cols:
                    col = 0
                    row += 1

            gb_layout.addLayout(badges_grid)
            self.badges_layout.addWidget(group_box)

    def switch_to_home_app(self, app_data):
            self.active_app_data = app_data
            
            detail_data_str = app_data.get("detailData", "{}")
            if isinstance(detail_data_str, str):
                try:
                    detail_data = json.loads(detail_data_str) if detail_data_str else {}
                except json.JSONDecodeError:
                    detail_data = {}
            else:
                detail_data = detail_data_str
                
            theme = detail_data.get("theme", "dark")
            text_color = "white" if theme != "dark" else "black"
            
            self.home_title.setStyleSheet(f"font-size: 48px; font-weight: bold; color: {text_color}; background-color: transparent;")
            self.home_desc.setStyleSheet(f"font-size: 16px; color: {text_color}; margin-top: 10px; background-color: transparent;")
            
            self.home_title.setText(app_data.get("libsTitles", "Untitled"))
            self.home_desc.setText(app_data.get("libsDesc", "No description available."))
            
            local_banner = app_data.get("local_banner")
            self.home_page.set_image(local_banner)
            
            lib_id = app_data.get("libsIds")
            is_running = lib_id in getattr(self, 'running_processes', {})
            state = self.get_app_install_state(app_data)
            
            if is_running:
                self.home_btn_action.setText("STOP")
                self.home_btn_action.setStyleSheet("QPushButton { background-color: #3c89e8; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #5e9dec; }")
                self.home_btn_update.hide()
            elif state == "LAUNCH":
                self.home_btn_action.setText("LAUNCH")
                self.home_btn_action.setStyleSheet("QPushButton { background-color: #5865f2; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #4752c4; }")
                self.home_btn_update.hide()
            elif state == "UPDATE":
                self.home_btn_action.setText("LAUNCH")
                self.home_btn_action.setStyleSheet("QPushButton { background-color: #5865f2; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #4752c4; }")
                self.home_btn_update.show()
            else:
                self.home_btn_action.setText("DOWNLOAD")
                self.home_btn_action.setStyleSheet("QPushButton { background-color: #00c853; color: black; font-size: 20px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #00e676; }")
                self.home_btn_update.hide()
                
            self.page_stack.setCurrentIndex(0)
    
    def switch_to_detail_page(self, app_data):
            self.active_app_data = app_data
            
            detail_data_str = app_data.get("detailData", "{}")
            if isinstance(detail_data_str, str):
                try:
                    detail_data = json.loads(detail_data_str) if detail_data_str else {}
                except json.JSONDecodeError:
                    detail_data = {}
            else:
                detail_data = detail_data_str
                
            version = detail_data.get("fdrLibs", {}).get("ver", "Unknown")
            self.lbl_version.setText(f"VERSION\n{version}")
            
            rollbacks = app_data.get("rollbacks", "")
            self.action_rollback.setVisible(bool(rollbacks))
            
            app_title = app_data.get("libsTitles", "Untitled")
            self.detail_title.setText(f"{app_title} - Settings")
            
            playtime = app_data.get("playtime", "0")
            last_login = app_data.get("last_login", "Never")
            self.lbl_playtime.setText(f"PLAY TIME\n{playtime} Hours")
            self.lbl_lastlogin.setText(f"LAST LOGIN\n{last_login}")
            
            local_banner = app_data.get("local_banner")
            self.detail_banner.set_image(local_banner)
            
            lib_id = app_data.get("libsIds")
            is_running = lib_id in getattr(self, 'running_processes', {})
            state = self.get_app_install_state(app_data)
            
            if is_running:
                self.btn_action.setText("STOP")
                self.btn_action.setStyleSheet("QPushButton { background-color: #3c89e8; color: white; font-size: 16px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #5e9dec; }")
                self.btn_update.hide()
                self.action_uninstall.setVisible(True)
            elif state == "LAUNCH":
                self.btn_action.setText("LAUNCH")
                self.btn_action.setStyleSheet("QPushButton { background-color: #5865f2; color: white; font-size: 16px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #4752c4; }")
                self.btn_update.hide()
                self.action_uninstall.setVisible(True)
            elif state == "UPDATE":
                self.btn_action.setText("LAUNCH")
                self.btn_action.setStyleSheet("QPushButton { background-color: #5865f2; color: white; font-size: 16px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #4752c4; }")
                self.btn_update.show()
                self.action_uninstall.setVisible(True)
            else:
                self.btn_action.setText("DOWNLOAD")
                self.btn_action.setStyleSheet("QPushButton { background-color: #00c853; color: black; font-size: 16px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #00e676; }")
                self.btn_update.hide()
                self.action_uninstall.setVisible(False)
                
            self.render_badges_section(lib_id)
            self.page_stack.setCurrentIndex(1)
    
    def set_custom_app_dir(self):
        if not self.active_app_data: return
        lib_id = self.active_app_data.get("libsIds")
        current_dir = APP_CONFIG["custom_dirs"].get(lib_id, APP_CONFIG["global_dir"])
        
        new_dir = QFileDialog.getExistingDirectory(self, "Select Custom Install Directory", current_dir)
        if new_dir:
            APP_CONFIG["custom_dirs"][lib_id] = os.path.join(new_dir, lib_id)
            save_config(APP_CONFIG)
            QMessageBox.information(self, "Path Updated", "Custom installation path saved for this application.")
            self.switch_to_home_app(self.active_app_data)

    def uninstall_app(self):
        if not self.active_app_data: return
        lib_id = self.active_app_data.get("libsIds")
        title = self.active_app_data.get("libsTitles", "App")
        
        fdr_libs = self.active_app_data.get("fdrLibs", "")
        rollbacks = self.active_app_data.get("rollbacks", "")
        file_details_str = self.active_app_data.get("detailData", "{}")
        
        try:
            file_details = json.loads(file_details_str) if file_details_str else {}
        except json.JSONDecodeError:
            file_details = {}

        curr_folder = fdr_libs.replace(".zip", "") if fdr_libs else ""
        prev_folder = rollbacks.replace(".zip", "") if rollbacks else ""
        install_path = APP_CONFIG["custom_dirs"].get(lib_id, os.path.join(APP_CONFIG["global_dir"], lib_id))
        
        curr_full_path = os.path.join(install_path, curr_folder) if curr_folder else ""
        prev_full_path = os.path.join(install_path, prev_folder) if prev_folder else ""
        
        active_path = None
        active_uninst = "none"
        
        if curr_full_path and os.path.exists(curr_full_path):
            active_path = curr_full_path
            active_uninst = file_details.get("fdrLibs", {}).get("uninst", "none")
        elif prev_full_path and os.path.exists(prev_full_path):
            active_path = prev_full_path
            active_uninst = file_details.get("rollbacks", {}).get("uninst", "none")
            
        if not active_path:
            QMessageBox.information(self, "Uninstall", "Application is not currently installed.")
            return

        reply = QMessageBox.question(self, "Uninstall", f"Are you sure you want to uninstall {title}?", 
                                     QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No)
        if reply == QMessageBox.StandardButton.Yes:
            try:
                uninst_exe = os.path.join(active_path, active_uninst)
                if active_uninst != "none" and os.path.exists(uninst_exe) and uninst_exe.endswith('.exe'):
                    os.startfile(uninst_exe) 
                else:
                    shutil.rmtree(active_path)
                    
                QMessageBox.information(self, "Uninstalled", f"{title} has been uninstalled successfully.")
                self.switch_to_detail_page(self.active_app_data)
            except Exception as e:
                QMessageBox.critical(self, "Error", f"Failed to perform uninstall process: {e}")

    def init_library_page(self):
        page = QWidget()
        layout = QVBoxLayout(page)
        layout.setContentsMargins(30, 30, 30, 30)
        
        title = QLabel("MarkedOut Library")
        title.setStyleSheet("font-size: 24px; font-weight: bold; color: white; margin-bottom: 20px;")
        layout.addWidget(title)
        
        scroll_area = QScrollArea()
        scroll_area.setWidgetResizable(True)
        scroll_area.setStyleSheet("QScrollArea { border: none; background-color: transparent; }")
        
        grid_widget = QWidget()
        grid_widget.setStyleSheet("background-color: transparent;")
        grid_layout = QGridLayout(grid_widget)
        grid_layout.setSpacing(20)
        grid_layout.setAlignment(Qt.AlignmentFlag.AlignTop | Qt.AlignmentFlag.AlignLeft)
        
        row, col = 0, 0
        max_cols = 4
        for lib_id, app in COLLECTION_RETURN.items():
            card = QPushButton()
            card.setFixedSize(240, 135)
            local_banner = app.get("local_banner")
            if local_banner and os.path.exists(local_banner):
                banner_path_qt = local_banner.replace('\\', '/')
                card.setStyleSheet(f"""
                    QPushButton {{
                        border-radius: 8px;
                        border-image: url({banner_path_qt}) 0 0 0 0 stretch stretch;
                    }}
                    QPushButton:hover {{ border: 2px solid #ffffff; }}
                """)
            else:
                card.setText(app.get("libsTitles", "Unknown"))
                card.setStyleSheet("background-color: #2b2d31; color: white; border-radius: 8px; font-weight: bold;")
            
            card.clicked.connect(lambda checked, data=app: self.switch_to_detail_page(data))
            grid_layout.addWidget(card, row, col)
            col += 1
            if col >= max_cols:
                col = 0
                row += 1
                
        scroll_area.setWidget(grid_widget)
        layout.addWidget(scroll_area)
        self.page_stack.addWidget(page)

    def init_downloads_page(self):
            page = QWidget()
            layout = QVBoxLayout(page)
            layout.setContentsMargins(30, 30, 30, 30)
            layout.setSpacing(15)
            title = QLabel("DOWNLOADS & UPDATES")
            title.setStyleSheet("font-size: 24px; font-weight: bold; color: white;")
            layout.addWidget(title)
            
            # Collection Info Banner
            self.dl_collection_title = QLabel("No active instance")
            self.dl_collection_title.setStyleSheet("font-size: 16px; font-weight: bold; color: #5865f2;")
            layout.addWidget(self.dl_collection_title)
            
            # Progress Bar
            self.dl_progress_bar = QProgressBar()
            self.dl_progress_bar.setFixedHeight(22)
            self.dl_progress_bar.setStyleSheet("""
                QProgressBar {
                    background-color: #1e1f22;
                    border: 1px solid #2b2d31;
                    border-radius: 4px;
                    color: white;
                    text-align: center;
                    font-weight: bold;
                }
                QProgressBar::chunk {
                    background-color: #5865f2;
                    border-radius: 3px;
                }
            """)
            self.dl_progress_bar.setValue(0)
            layout.addWidget(self.dl_progress_bar)
    
            # Download Log
            self.downloads_list = QListWidget()
            self.downloads_list.setStyleSheet("background-color: #1e1f22; border-radius: 4px; padding: 10px;")
            self.downloads_list.addItem("No active download processes running.")
            layout.addWidget(self.downloads_list)
            
            layout.addStretch()
            self.page_stack.addWidget(page)
    
    def init_settings_page(self):
        page = QWidget()
        layout = QVBoxLayout(page)
        layout.setContentsMargins(40, 40, 40, 40)
        layout.setSpacing(20)
        title = QLabel("Profile & Settings")
        title.setStyleSheet("font-size: 28px; font-weight: bold; color: white;")
        layout.addWidget(title)

        # pf card
        profile_card = QFrame()
        profile_card.setStyleSheet("background-color: #1e1f22; border-radius: 8px;")
        profile_layout = QHBoxLayout(profile_card)
        profile_layout.setContentsMargins(20, 15, 20, 15)
        profile_layout.setSpacing(15)
        # icon
        profile_icon = QLabel()
        profile_icon.setFixedSize(64, 64)
        profile_icon.setAlignment(Qt.AlignmentFlag.AlignCenter)
        attach_name = PROFILE_CACHE.get("profileAttachs", "")
        profile_icon_file = CACHE_DIR / attach_name if attach_name else None
        if profile_icon_file and profile_icon_file.exists():
            pix = QPixmap(str(profile_icon_file)).scaled(
                64, 64, Qt.AspectRatioMode.KeepAspectRatioByExpanding, Qt.TransformationMode.SmoothTransformation
            )
            profile_icon.setPixmap(pix)
        else:
            initial = PROFILE_CACHE.get("profileNames", "U")[0].upper()
            profile_icon.setText(initial)
            profile_icon.setStyleSheet("""
                background-color: #5865f2; color: white; 
                font-size: 26px; font-weight: bold; border-radius: 32px;
            """)
        profile_layout.addWidget(profile_icon)

        # pf info
        meta_layout = QVBoxLayout()
        meta_layout.setSpacing(2)
        name_tag_layout = QHBoxLayout()
        name_lbl = QLabel(PROFILE_CACHE.get("profileNames", "User"))
        name_lbl.setStyleSheet("font-size: 18px; font-weight: bold; color: white;")
        tag_lbl = QLabel(f"@{PROFILE_CACHE.get('profileTags', 'unknown')}")
        tag_lbl.setStyleSheet("font-size: 13px; color: #b5bac1; margin-top: 2px;")
        name_tag_layout.addWidget(name_lbl)
        name_tag_layout.addWidget(tag_lbl)
        name_tag_layout.addStretch()
        jdate_lbl = QLabel(f"Member since: {PROFILE_CACHE.get('profileJDates', 'N/A')}")
        jdate_lbl.setStyleSheet("font-size: 11px; color: #949ba4;")

        meta_layout.addLayout(name_tag_layout)
        meta_layout.addWidget(jdate_lbl)
        profile_layout.addLayout(meta_layout)
        profile_layout.addStretch()
        layout.addWidget(profile_card)
        
        # Configs
        btn_style = """
            QPushButton { background-color: #2b2d31; color: white; border-radius: 6px; padding: 10px 15px; font-weight: bold; font-size: 14px; }
            QPushButton:hover { background-color: #4f545c; }
        """
        danger_style = """
            QPushButton { background-color: #f04747; color: white; border-radius: 6px; padding: 10px; font-weight: bold; font-size: 14px; }
            QPushButton:hover { background-color: #d83c3e; }
        """
        input_style = "QLineEdit { background-color: #1e1f22; border: 1px solid #2b2d31; border-radius: 6px; padding: 10px; color: white; font-size: 14px; }"
        dir_layout = QHBoxLayout()
        dir_label = QLabel("Global Install Path:")
        dir_label.setStyleSheet("font-size: 14px; color: #b5bac1; font-weight: bold;")
        
        self.dir_input = QLineEdit(APP_CONFIG.get("global_dir", ""))
        self.dir_input.setReadOnly(True)
        self.dir_input.setStyleSheet(input_style)

        dir_btn = QPushButton("Change Directory")
        dir_btn.setStyleSheet(btn_style)
        dir_btn.clicked.connect(self.change_global_dir)
        
        dir_layout.addWidget(dir_label)
        dir_layout.addWidget(self.dir_input)
        dir_layout.addWidget(dir_btn)
        layout.addLayout(dir_layout)
        
        sys_tray_check = QCheckBox("Minimize to system tray on close")
        sys_tray_check.setChecked(True)
        sys_tray_check.setStyleSheet("QCheckBox { font-size: 14px; color: #e3e3e3; } QCheckBox::indicator { width: 18px; height: 18px; }")
        layout.addWidget(sys_tray_check)
        
        logout_btn = QPushButton("Sign Out / Clear Session")
        logout_btn.setFixedSize(260, 45)
        logout_btn.setStyleSheet(danger_style)
        logout_btn.clicked.connect(self.handle_logout)
        layout.addWidget(logout_btn)
        
        layout.addStretch()
        self.page_stack.addWidget(page)

    def change_global_dir(self):
        new_dir = QFileDialog.getExistingDirectory(self, "Select Install Directory", APP_CONFIG["global_dir"])
        if new_dir:
            APP_CONFIG["global_dir"] = new_dir
            save_config(APP_CONFIG)
            self.dir_input.setText(new_dir)

    def handle_app_action(self, forced_action=None):
            if not self.active_app_data: return
            
            lib_id = self.active_app_data.get("libsIds")
            title = self.active_app_data.get("libsTitles", "App")
            fdr_libs = self.active_app_data.get("fdrLibs", "")
            rollbacks = self.active_app_data.get("rollbacks", "")
            if forced_action:
                btn_text = forced_action
            else:
                sender_btn = self.sender()
                btn_text = sender_btn.text() if sender_btn else self.btn_action.text()
            
            if btn_text in ["DOWNLOAD", "UPDATE"]:
                install_path = APP_CONFIG["custom_dirs"].get(lib_id, os.path.join(APP_CONFIG["global_dir"], lib_id))
                self.btn_action.setEnabled(False)
                if hasattr(self, 'home_btn_action'):
                    self.home_btn_action.setEnabled(False)
                if hasattr(self, 'btn_update'):
                    self.btn_update.setEnabled(False)
                if hasattr(self, 'home_btn_update'):
                    self.home_btn_update.setEnabled(False)
                
                # Setup Downloads Page Header and Bar
                self.dl_collection_title.setText(f"Active Task: {title} ({btn_text})")
                self.dl_progress_bar.setValue(0)
                self.downloads_list.clear()
                self.downloads_list.addItem(f"Downloading {title}...")
                self.page_stack.setCurrentIndex(3)
                
                detail_data_str = self.active_app_data.get("detailData", "{}")
                if isinstance(detail_data_str, str):
                    try:
                        detail_data = json.loads(detail_data_str) if detail_data_str else {}
                    except json.JSONDecodeError:
                        detail_data = {}
                else:
                    detail_data = detail_data_str
                    
                version = detail_data.get("fdrLibs", {}).get("ver", "1.0.0")
                
                is_update = (btn_text == "UPDATE")
                old_folder = rollbacks.replace(".zip", "") if rollbacks else ""
                token = load_token()
                
                self.worker = DownloadWorker(lib_id, install_path, fdr_libs, token, version, is_update, old_folder, "fdrLibs")
                self.worker.status_update.connect(lambda msg: self.downloads_list.addItem(msg))
                self.worker.progress.connect(self.update_progress)
                self.worker.finished.connect(self.on_download_finished)
                self.worker.start()
                
            elif btn_text == "LAUNCH":
                logging.info(f"Preparing to launch: {title}")
                
                detail_data_str = self.active_app_data.get("detailData", "{}")
                if isinstance(detail_data_str, str):
                    try:
                        detail_data = json.loads(detail_data_str) if detail_data_str else {}
                    except json.JSONDecodeError:
                        detail_data = {}
                else:
                    detail_data = detail_data_str
    
                exe_name = detail_data.get("fdrLibs", {}).get("executables")
                if not exe_name:
                    exe_name = detail_data.get("rollbacks", {}).get("executables")
                
                if not exe_name:
                    QMessageBox.critical(self, "Launch Error", f"No executable defined for {title}.")
                    return
    
                install_path = APP_CONFIG["custom_dirs"].get(lib_id, os.path.join(APP_CONFIG["global_dir"], lib_id))
                exe_path = None
                
                for root, dirs, files in os.walk(install_path):
                    if exe_name in files:
                        exe_path = os.path.join(root, exe_name)
                        break
                
                if not exe_path:
                    QMessageBox.critical(self, "Launch Error", f"Could not find {exe_name} in {install_path}.\nYou may need to verify your installation.")
                    return
    
                try:
                    env = os.environ.copy()
                    env["CGCC_PORT"] = str(GLOBAL_PORT)
                    proc = subprocess.Popen([exe_path], cwd=os.path.dirname(exe_path), env=env)
                    self.running_processes[lib_id] = proc
                    
                    self.process_timers[lib_id] = QTimer(self)
                    self.process_timers[lib_id].timeout.connect(lambda l_id=lib_id: self.check_process_status(l_id))
                    self.process_timers[lib_id].start(2000) 
    
                    stop_style = "QPushButton { background-color: #3c89e8; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #5e9dec; }"
                    self.btn_action.setText("STOP")
                    self.btn_action.setStyleSheet(stop_style)
                    self.home_btn_action.setText("STOP")
                    self.home_btn_action.setStyleSheet(stop_style)
                    self.btn_update.hide()
                    self.home_btn_update.hide()
                    
                except Exception as e:
                    logging.error(f"Failed to launch {exe_path}: {e}")
                    QMessageBox.critical(self, "Launch Error", f"Failed to launch application:\n{str(e)}")
    
            elif btn_text == "STOP":
                logging.info(f"Stopping: {title}")
                proc = self.running_processes.get(lib_id)
                if proc:
                    try:
                        proc.terminate()
                        proc.wait(timeout=3)
                    except subprocess.TimeoutExpired:
                        proc.kill()
                    except Exception as e:
                        logging.error(f"Error while stopping process: {e}")
                    
                    if lib_id in self.running_processes:
                        del self.running_processes[lib_id]
                    if lib_id in self.process_timers:
                        self.process_timers[lib_id].stop()
                        del self.process_timers[lib_id]
    
                launch_style = "QPushButton { background-color: #5865f2; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; } QPushButton:hover { background-color: #4752c4; }"
                self.btn_action.setText("LAUNCH")
                self.btn_action.setStyleSheet(launch_style)
                self.home_btn_action.setText("LAUNCH")
                self.home_btn_action.setStyleSheet(launch_style)
    
    def rollback_app(self):
            if not self.active_app_data: return
            lib_id = self.active_app_data.get("libsIds")
            title = self.active_app_data.get("libsTitles", "App")
            fdr_libs = self.active_app_data.get("fdrLibs", "")
            rollbacks = self.active_app_data.get("rollbacks", "")
            
            msgBox = QMessageBox(self)
            msgBox.setWindowTitle("Rollback?")
            msgBox.setText(
                f"Are you sure you want to rollback {title} to the previous version?"
            )
            msgBox.setIcon(QMessageBox.Icon.Question)
            msgBox.setStandardButtons(
                QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No
            )
            msgBox.setStyleSheet("""
                QMessageBox {
                    background-color: #2b2d31;
                }
                QLabel {
                    background-color: #2b2d31;
                    color: #ffffff;
                    font-size: 14px;
                }
                QPushButton {
                    background-color: #383a40;
                    color: white;
                    border-radius: 4px;
                    padding: 6px 12px;
                    font-weight: bold;
                }
                QPushButton:hover {
                    background-color: #5865f2;
                }
            """)
            reply = msgBox.exec()
            if (
                reply != QMessageBox.StandardButton.Yes
            ):
                return
    
            install_path = APP_CONFIG["custom_dirs"].get(lib_id, os.path.join(APP_CONFIG["global_dir"], lib_id))
            
            self.btn_action.setEnabled(False)
            if hasattr(self, 'home_btn_action'):
                self.home_btn_action.setEnabled(False)
            if hasattr(self, 'btn_update'):
                self.btn_update.setEnabled(False)
            if hasattr(self, 'home_btn_update'):
                self.home_btn_update.setEnabled(False)
    
            # Config Downloads Page & Switch to Download Page
            self.dl_collection_title.setText(f"Active Task: {title} (ROLLBACK)")
            self.dl_progress_bar.setValue(0)
            
            self.downloads_list.clear()
            self.downloads_list.addItem(f"Rolling back {title}...")
            
            self.page_stack.setCurrentIndex(3)
            
            detail_data_str = self.active_app_data.get("detailData", "{}")
            if isinstance(detail_data_str, str):
                try:
                    detail_data = json.loads(detail_data_str) if detail_data_str else {}
                except json.JSONDecodeError:
                    detail_data = {}
            else:
                detail_data = detail_data_str
                
            version = detail_data.get("rollbacks", {}).get("ver", "unknown")
            old_folder = fdr_libs.replace(".zip", "") if fdr_libs else ""
            token = load_token()
            
            self.worker = DownloadWorker(lib_id, install_path, rollbacks, token, version, True, old_folder, "rollbacks")
            self.worker.status_update.connect(lambda msg: self.downloads_list.addItem(msg))
            self.worker.progress.connect(self.update_progress)
            self.worker.finished.connect(self.on_download_finished)
            self.worker.start()
     
    def update_progress(self, val):
            self.dl_progress_bar.setValue(val)
            item = self.downloads_list.item(0)
            if item:
                title = self.active_app_data.get("libsTitles", "App") if self.active_app_data else "App"
                item.setText(f"Processing {title}... ({val}%)")
                logging.info(f"Processing {title}... ({val}%)")
    
    def on_download_finished(self, success, message):
        self.btn_action.setEnabled(True)
        if hasattr(self, 'home_btn_action'):
            self.home_btn_action.setEnabled(True)
        if hasattr(self, 'btn_update'):
            self.btn_update.setEnabled(True)
        if hasattr(self, 'home_btn_update'):
            self.home_btn_update.setEnabled(True)
            
        if success:
            success_msg = "Download & Installation completed successfully."
            self.downloads_list.addItem(success_msg)
            logging.info(success_msg)
            self.dl_collection_title.setText("Download Completed")
            
            if self.active_app_data:
                self.switch_to_detail_page(self.active_app_data)
        else:
            error_msg = f"Download process failed: {message}"
            self.downloads_list.addItem(error_msg)
            logging.error(error_msg)
            self.dl_collection_title.setText("Download Failed")

    def handle_logout(self):
        config = APP_CONFIG
        config["session_token"] = ""
        save_config(config)
        msgBox = QMessageBox(self)
        msgBox.setWindowTitle("Logged Out")
        msgBox.setText(
            "Session cache cleared successfully. Restart app required."
        )
        msgBox.setStandardButtons(
            QMessageBox.StandardButton.Ok
        )
        msgBox.setStyleSheet("""
            QMessageBox {
                background-color: #2b2d31;
            }
            QLabel {
                background-color: #2b2d31;
                color: #ffffff;
                font-size: 14px;
            }
            QPushButton {
                background-color: #383a40;
                color: white;
                border-radius: 4px;
                padding: 6px 12px;
                font-weight: bold;
            }
            QPushButton:hover {
                background-color: #5865f2;
            }
        """)
        msgBox.exec()
        os._exit(0)

    def heartbeat(self):
        self.heartbeat_timers = QTimer(self)
        self.heartbeat_timers.timeout.connect(self.send_heartbeat)
        self.heartbeat_timers.start(60000)

    def send_heartbeat(self):
        token = load_token()
        if not token:
            return

        is_run = 1 if self.running_processes else 0
        target = "None"
        if is_run:
            target = list(self.running_processes.keys())[0] 
            
        payload = {
            "isRun": is_run,
            "target": target,
            "tokens": token,
            "address": get_device_id(),
            "os": platform.system()
        }
        
        headers = {"X-Api-Key": API_KEY, "Content-Type": "application/json"}
        try:
            response = requests.post(API_URL_HEARTBEAT, json=payload, headers=headers, timeout=10)
            if response.status_code == 200:
                if not response.text or not response.text.strip():
                    logging.warning("Heartbeat acknowledged(status:200), empty response body.")
                    return

                try:
                    data = response.json()
                except ValueError as json_err:
                    logging.error(f"Heartbeat returned non-JSON response: '{response.text}' - Error: {json_err}")
                    return
                
                if "newData" in data and target != "None":
                    marked_data = data["newData"].get("marked", {})
                    
                    if target in marked_data:
                        stats = marked_data[target]
                        new_hours = stats.get("Hours", 0)
                        new_last_log = stats.get("lastLog", "Never")
                        if self.active_app_data and self.active_app_data.get("libsIds") == target:
                            self.lbl_playtime.setText(f"PLAY TIME\n{new_hours} Hours")
                            self.lbl_lastlogin.setText(f"LAST LOGIN\n{new_last_log}")
                        if target in COLLECTION_RETURN:
                            COLLECTION_RETURN[target]["playtime"] = str(new_hours)
                            COLLECTION_RETURN[target]["last_login"] = new_last_log
                            
        except Exception as e:
            logging.error(f"Heartbeat sync failed: {e}")

    def closeEvent(self, event):
        event.ignore()
        self.hide()

# Tray Controller
class SystemTrayApp:
    def __init__(self, app_instance, launcher_ui):
        self.app = app_instance
        self.launcher_ui = launcher_ui
        
        icon_path = BASE_DIR / "asset/cgcclogotrsp.ico"
        if icon_path.exists():
            tray_icon_image = QIcon(str(icon_path))
        else:
            logging.info("Generating native fallback tray icon.")
            pixmap = QPixmap(64, 64)
            pixmap.fill(QColor(43, 45, 49)) 
            
            painter = QPainter(pixmap)
            painter.setPen(QColor(255, 255, 255))
            font = QFont("Arial", 14, QFont.Weight.Bold)
            painter.setFont(font)
            painter.drawText(pixmap.rect(), Qt.AlignmentFlag.AlignCenter, "CGCC")
            painter.end()
            
            tray_icon_image = QIcon(pixmap)
        
        self.tray_icon = QSystemTrayIcon(tray_icon_image, self.app)
        self.tray_icon.setToolTip("CGCC")
        
        menu = QMenu()
        open_action = QAction("Open Menu", self.app)
        open_action.triggered.connect(lambda: self.navigate_and_show("HOME"))
        
        lib_action = QAction("Library", self.app)
        lib_action.triggered.connect(lambda: self.navigate_and_show("LIBRARY"))
        
        dl_action = QAction("Downloads", self.app)
        dl_action.triggered.connect(lambda: self.navigate_and_show("DOWNLOADS"))
        
        settings_action = QAction("Settings", self.app)
        settings_action.triggered.connect(lambda: self.navigate_and_show("SETTINGS"))
        
        exit_action = QAction("Exit", self.app)
        exit_action.triggered.connect(self.hard_exit)
        
        menu.addActions([open_action, lib_action, dl_action, settings_action])
        menu.addSeparator()
        menu.addAction(exit_action)
        
        self.tray_icon.setContextMenu(menu)
        self.tray_icon.show()
        
        signals.navigate_ui.connect(self.navigate_and_show)

    def navigate_and_show(self, cmd):
        self.launcher_ui.showNormal()
        self.launcher_ui.activateWindow()
        
        if cmd == "HOME": self.launcher_ui.page_stack.setCurrentIndex(0)
        elif cmd == "LIBRARY": self.launcher_ui.page_stack.setCurrentIndex(2)
        elif cmd == "DOWNLOADS": self.launcher_ui.page_stack.setCurrentIndex(3)
        elif cmd == "SETTINGS": self.launcher_ui.page_stack.setCurrentIndex(4)

    def hard_exit(self):
        logging.info("Executing Tray Exit. Hard stopping processes.")
        self.tray_icon.hide()
        os._exit(0)

# Routines
class LauncherController:
    def __init__(self):
        self.qapp = QApplication(sys.argv)
        self.qapp.setQuitOnLastWindowClosed(False)

        global GLOBAL_PORT
        GLOBAL_PORT = find_free_port()
        threading.Thread(target=run_flask_server, args=(GLOBAL_PORT,), daemon=True).start()
        print(f"HELPER_PORT={GLOBAL_PORT}", flush=True)

        self.launcher_ui = LauncherApp()
        self.tray = SystemTrayApp(self.qapp, self.launcher_ui)
        self.startup = StartupFlow(self)
        self.startup.show()
        
    def initialize_main_ui(self):
        self.launcher_ui.build_ui()
        self.launcher_ui.show()

if __name__ == "__main__":
    controller = LauncherController()
    sys.exit(controller.qapp.exec())