# note: make sure to always run this and not the other program, if somehow the startup program stuck just hardkill it via terminal

import sys
import subprocess
import requests
import logging
import json
from pathlib import Path
from PyQt6.QtWidgets import (
    QApplication, QWidget, QVBoxLayout, QLabel, QLineEdit, QPushButton, 
    QCheckBox, QMainWindow, QMessageBox
)
from PyQt6.QtGui import QMovie
from PyQt6.QtCore import Qt, QTimer

logging.basicConfig(
    filename='launcher_helper.log',
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

BASE_DIR = Path(__file__).parent.resolve()
TOKEN_FILE = BASE_DIR / ("session.json")
TEST_FILE = BASE_DIR / ("test.json")
def save_token(token):
    if token:
        TOKEN_FILE.write_text(json.dumps({"sessionToken": token}))
    else:
        logging.error("empty/null session token")

def load_token():
    if TOKEN_FILE.exists():
        try:
            data = json.loads(TOKEN_FILE.read_text())
            return data.get("sessionToken")
        except json.JSONDecodeError:
            return None
    return None

class StartupFlow(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowFlags(Qt.WindowType.FramelessWindowHint)
        self.resize(400, 500)
        self.setStyleSheet("background-color: #111213; color: white; font-family: 'Segoe UI';")
        
        self.layout = QVBoxLayout()
        self.layout.setAlignment(Qt.AlignmentFlag.AlignCenter)
        
        # Loading animation
        self.gif_label = QLabel()
        self.gif_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.movie = QMovie("loading.gif") # change tis if needed
        if self.movie.isValid():
            self.gif_label.setMovie(self.movie)
            self.movie.start()
        else:
            self.gif_label.setText("[ LOADING... ]")
            self.gif_label.setStyleSheet("font-size: 24px; color: #5865f2; font-weight: bold;")
        self.layout.addWidget(self.gif_label)

        self.status_label = QLabel("Phase 1: Starting Background Helper...")
        self.status_label.setStyleSheet("font-size: 16px; font-weight: bold; margin-top: 20px;")
        self.status_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.layout.addWidget(self.status_label)
        
        container = QWidget()
        container.setLayout(self.layout)
        self.setCentralWidget(container)
        
        self.helper_port = None
        QTimer.singleShot(800, self.start_helper) # Slight delay

    def start_helper(self):
        self.helper_process = subprocess.Popen(
            [sys.executable, "helper.py"],
            stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True
        )
        
        first_line = self.helper_process.stdout.readline()
        if "HELPER_PORT=" in first_line:
            self.helper_port = first_line.split("=")[1].strip()
            self.status_label.setText("Phase 2: Verifying Session...")
            QTimer.singleShot(1000, self.verify_session)
        else:
            logging.info("Error: Could not connect to background helper")
            self.status_label.setText("Error: Could not connect to background helper")

    def verify_session(self):
        saved_token = load_token()
        if not saved_token:
            self.show_login_ui()
            return
        try:
            response = requests.put(
                f"http://127.0.0.1:{self.helper_port}/verifysession", 
                json={"tokens": saved_token}
            )
            data = response.json()
            datas = data.get("data")
            if data.get("success"):
                self.launch_main_client(datas)
            else:
                self.show_login_ui()
        except requests.ConnectionError:
            self.status_label.setText("Phase 2 Failed: API Unreachable.")
            logging.info("Error: API unreachable")

    def show_login_ui(self):
        for i in reversed(range(self.layout.count())): 
            self.layout.itemAt(i).widget().setParent(None)

        self.layout.addWidget(QLabel("<h2 style='text-align:center;'>LOGIN REQUIRED</h2>"))
        
        self.user_input = QLineEdit()
        self.user_input.setPlaceholderText("Account Name")
        self.user_input.setStyleSheet("padding: 10px; background: #1e1f22; border-radius: 4px;")
        
        self.pass_input = QLineEdit()
        self.pass_input.setPlaceholderText("Password")
        self.pass_input.setEchoMode(QLineEdit.EchoMode.Password)
        self.pass_input.setStyleSheet("padding: 10px; background: #1e1f22; border-radius: 4px;")
        
        self.stay_signed_in = QCheckBox("Keep me signed in")
        
        self.login_btn = QPushButton("Sign In")
        self.login_btn.setStyleSheet("background-color: #2979ff; padding: 10px; border-radius: 4px; font-weight: bold;")
        self.login_btn.clicked.connect(self.process_login)
        
        self.layout.addWidget(self.user_input)
        self.layout.addWidget(self.pass_input)
        self.layout.addWidget(self.stay_signed_in)
        self.layout.addWidget(self.login_btn)

    def process_login(self):
            self.login_btn.setText("Signing in...")
            payload = {
                "username": self.user_input.text(),
                "password": self.pass_input.text(),
                "sessionless": True
                # "sessionless": not self.stay_signed_in.isChecked()
            }
            try:
                response = requests.post(f"http://127.0.0.1:{self.helper_port}/login", json=payload)
                data = response.json()
                
                if data.get("success"):
                    # (Assumed sessionless=True when no token is returned)
                    if not payload["sessionless"] and "sessionToken" in data:
                        save_token(data.get("sessionToken"))

                    # for debugging the returned value
                    # TEST_FILE.write_text(json.dumps(data))
                    datas = data.get("data")
                    self.launch_main_client(datas)
                else:
                    self.login_btn.setText("Sign In")
                    QMessageBox.critical(self, "Login Error", data.get("error", "Invalid Credentials"))
            except requests.ConnectionError:
                QMessageBox.critical(self, "Connection Error", "Helper API offline.")
                logging.info("Error: Helper API offline")
                self.login_btn.setText("Sign In")


    def launch_main_client(self, datas):
        self.status_label = QLabel("Phase 3: Preparing UI...")
        self.status_label.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.setCentralWidget(self.status_label)
        subprocess.Popen([sys.executable, "client.py", str(self.helper_port)])
        self.close()

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = StartupFlow()
    window.show()
    sys.exit(app.exec())