# this UI still uses mock data on the MOCK_APPS array below

import sys
import os
import requests
import logging
from PyQt6.QtWidgets import (
    QApplication, QMainWindow, QWidget, QHBoxLayout, QVBoxLayout, 
    QPushButton, QLabel, QStackedWidget, QListWidget, QListWidgetItem,
    QFileDialog, QLineEdit, QFrame, QSplitter, QProgressBar, QScrollArea
)
from PyQt6.QtCore import Qt, QTimer

HELPER_PORT = sys.argv[1] if len(sys.argv) > 1 else "8080"
INITIAL_PAGE = sys.argv[2] if len(sys.argv) > 2 else "HOME"

logging.basicConfig(
    filename='launcher_helper.log',
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)


# use this for testing UI w'out api's
MOCK_APPS = [
    {
    "id": "NIE", "title": "NamelessInExistence", "desc": "Within the nothingness. everything that can, will exist",
    "status": "Ready to Play", "banner_color": "#4f83d6", "icon_text": "NIE", "banner": "test.png"
    }
]

# yes just reuse these things
OPTIONS = [
    {"id": "1", "title": "Library", "icon_img": "lbr.svg", "icon_text": "LBR"},
    {"id": "2", "title": "Downloads", "icon_img": "dl.svg", "icon_text": "DL"},
    {"id": "3", "title": "Settings", "icon_img": "stg.svg", "icon_text": "STG"},
]

class LauncherApp(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Launcher")
        self.resize(1366, 768)
        self.download_directory = os.path.expanduser("~/Downloads")
        
        self.setStyleSheet("""
            QMainWindow { background-color: #111213; }
            QWidget { color: #e3e3e3; font-family: 'Segoe UI', sans-serif; }
            QLineEdit { background-color: #1e1f22; border: 1px solid #2b2d31; border-radius: 4px; padding: 6px; color: white; }
            QListWidget { background-color: transparent; border: none; }
            QProgressBar { border: 1px solid #2b2d31; border-radius: 4px; text-align: center; background-color: #1e1f22; color: white; }
            QProgressBar::chunk { background-color: #2979ff; }
        """)

        main_layout = QHBoxLayout()
        main_layout.setContentsMargins(0, 0, 0, 0)
        main_layout.setSpacing(0)

        # app icon bar
        self.icon_bar = QWidget()
        self.icon_bar.setFixedWidth(65)
        self.icon_bar.setStyleSheet("background-color: #1e1f22; border-right: 1px solid #2b2d31;")
        icon_layout = QVBoxLayout(self.icon_bar)
        icon_layout.setContentsMargins(5, 15, 5, 15)
        icon_layout.setAlignment(Qt.AlignmentFlag.AlignTop)

        for app_data in MOCK_APPS:
            btn = QPushButton(app_data["icon_text"])
            btn.setFixedSize(55, 55)
            btn.setStyleSheet(f"background-color: {app_data['banner_color']}; color: white; border-radius: 8px; font-weight: bold;")
            btn.clicked.connect(lambda checked, data=app_data: self.switch_to_home_app(data))
            icon_layout.addWidget(btn)

        # Pages
        self.page_stack = QStackedWidget()
        self.init_home_page()       
        self.init_library_page()    
        self.init_downloads_page()  
        self.init_settings_page()   
        
        # Vertical navbar
        self.options_bar = QWidget()
        self.options_bar.setFixedWidth(40)
        self.options_bar.setStyleSheet("background-color: #ffffff; border-left: 1px solid #ffffff;")
        options_layout = QVBoxLayout(self.options_bar)
        options_layout.setContentsMargins(5, 15, 5, 15)
        options_layout.setSpacing(10)
        options_layout.setAlignment(Qt.AlignmentFlag.AlignTop)

        # Mapping options (Library=1, Downloads=2, Settings=3)
        for index, opt_data in enumerate(OPTIONS):
            btn = QPushButton("")
            btn.setFixedSize(30, 30)
            # Used border-image to stretch the png over the button. 
            # If the image isn't found, it defaults to the background color and shows the icon_text.
            btn.setStyleSheet(f"""
                QPushButton {{
                    color: #b5bac1;
                    font-weight: bold; border-radius: 8px; font-size: 14px; border: 2px solid transparent;
                    border-image: url({opt_data['icon_img']}) 0 0 0 0 stretch stretch;
                }}
                QPushButton:hover {{ 
                    border: 2px solid #ffffff; background-color: #ffffff; color: white;
                }}
            """)
            
            # The page stack index is offset by 1 because Index 0 is the Home Page
            btn.clicked.connect(lambda checked, idx=index: self.page_stack.setCurrentIndex(idx + 1))
            options_layout.addWidget(btn)

        main_layout.addWidget(self.icon_bar)
        main_layout.addWidget(self.page_stack)
        main_layout.addWidget(self.options_bar)

        central_widget = QWidget()
        central_widget.setLayout(main_layout)
        self.setCentralWidget(central_widget)

        if INITIAL_PAGE == "LIBRARY": 
            self.page_stack.setCurrentIndex(1)
        elif INITIAL_PAGE == "DOWNLOADS": 
            self.page_stack.setCurrentIndex(2)
        elif INITIAL_PAGE == "SETTINGS": 
            self.page_stack.setCurrentIndex(3)
        else:
            if MOCK_APPS:
                self.switch_to_home_app(MOCK_APPS[0])

        # Polling Timer for Helper Connection and Tray Commands
        self.health_timer = QTimer(self)
        self.health_timer.timeout.connect(self.poll_helper)
        self.health_timer.start(500)

    def poll_helper(self):
        try:
            resp = requests.get(f"http://127.0.0.1:{HELPER_PORT}/get_command", timeout=1)
            data = resp.json()
            cmd = data.get("command")
            
            # Bring window to front
            if cmd:
                self.showNormal()
                self.activateWindow()
                
                # Switch pages set from tray command
                if cmd == "HOME": self.page_stack.setCurrentIndex(0)
                elif cmd == "LIBRARY": self.page_stack.setCurrentIndex(1)
                elif cmd == "DOWNLOADS": self.page_stack.setCurrentIndex(2)
                elif cmd == "SETTINGS": self.page_stack.setCurrentIndex(3)

        except requests.ConnectionError:
            logging.info("Helper closed. Terminating UI...")
            QApplication.quit()

    # --- HOME PAGE ---
    def init_home_page(self):
        self.home_page = QFrame()
        layout = QVBoxLayout(self.home_page)
        layout.setContentsMargins(30, 40, 30, 40)
        
        self.home_title = QLabel("Title")
        self.home_title.setStyleSheet("font-size: 32px; font-weight: bold; color: white;")
        layout.addWidget(self.home_title)
        
        self.home_desc = QLabel("Desc")
        self.home_desc.setWordWrap(True)
        self.home_desc.setFixedWidth(400)
        self.home_desc.setStyleSheet("font-size: 14px; color: white; margin-top: 10px;")
        layout.addWidget(self.home_desc)
        layout.addStretch() 
        
        launch_layout = QHBoxLayout()
        self.btn_launch = QPushButton("LAUNCH")
        self.btn_launch.setFixedSize(220, 60)
        launch_layout.addWidget(self.btn_launch)
        launch_layout.addStretch()
        layout.addLayout(launch_layout)
        self.page_stack.addWidget(self.home_page)

    def switch_to_home_app(self, app_data):
        self.home_title.setText(app_data["title"])
        self.home_desc.setText(app_data["desc"])
        
        if app_data["status"] == "Ready to Play":
            self.btn_launch.setText("LAUNCH")
            self.btn_launch.setStyleSheet("background-color: #00c853; color: black; font-size: 20px; font-weight: bold; border-radius: 6px;")
        elif app_data["status"] == "Update Available":
            self.btn_launch.setText("UPDATE")
            self.btn_launch.setStyleSheet("background-color: #ff9100; color: white; font-size: 20px; font-weight: bold; border-radius: 6px;")
        else:
            self.btn_launch.setText("DOWNLOAD")
            self.btn_launch.setStyleSheet("background-color: #2979ff; color: white; font-size: 20px; font-weight: bold; border-radius: 6px;")

        self.home_page.setStyleSheet(f"background-color: {app_data['banner_color']};")
        self.page_stack.setCurrentIndex(0)

    # --- LIBRARY PAGE ---
    def init_library_page(self):
        page = QWidget()
        layout = QHBoxLayout(page)
        layout.setContentsMargins(0,0,0,0)
        layout.setSpacing(0)
        
        splitter = QSplitter(Qt.Orientation.Horizontal)
        
        self.lib_list = QListWidget()
        self.lib_list.setStyleSheet("""
            QListWidget::item { padding: 15px; color: #b5bac1; border-bottom: 1px solid #1e1f22; }
            QListWidget::item:selected { background-color: #2b2d31; color: white; font-weight: bold; }
        """)
        
        for app in MOCK_APPS:
            item = QListWidgetItem(app["title"])
            item.setData(Qt.ItemDataRole.UserRole, app)
            self.lib_list.addItem(item)
            
        self.lib_list.currentItemChanged.connect(self.update_library_detail)
        splitter.addWidget(self.lib_list)
        
        self.detail_panel = QFrame()
        self.detail_panel.setStyleSheet("background-color: #111213; padding: 20px;")
        self.detail_layout = QVBoxLayout(self.detail_panel)
        self.detail_layout.setAlignment(Qt.AlignmentFlag.AlignTop)
        
        self.detail_title = QLabel("Select an app")
        self.detail_title.setStyleSheet("font-size: 22px; font-weight: bold; color: white;")
        self.detail_layout.addWidget(self.detail_title)
        
        self.detail_status = QLabel("")
        self.detail_status.setStyleSheet("color: #b5bac1; font-size: 14px; margin-top: 5px;")
        self.detail_layout.addWidget(self.detail_status)
        
        splitter.addWidget(self.detail_panel)
        splitter.setSizes([400, 600])
        layout.addWidget(splitter)
        self.page_stack.addWidget(page)
        
        if self.lib_list.count() > 0: self.lib_list.setCurrentRow(0)

    def update_library_detail(self, current, previous):
        if not current: return
        app_data = current.data(Qt.ItemDataRole.UserRole)
        self.detail_title.setText(app_data["title"])
        self.detail_status.setText(f"Status: {app_data['status']}\n\nInstall Path:\n{self.download_directory}/{app_data['title']}")

    # --- DOWNLOADS PAGE ---
    def init_downloads_page(self):
        page = QWidget()
        layout = QVBoxLayout(page)
        layout.setContentsMargins(30, 30, 30, 30)
        
        header = QLabel("MANAGE DOWNLOADS")
        header.setStyleSheet("font-size: 24px; font-weight: bold; color: white; padding-bottom: 10px;")
        layout.addWidget(header)
        
        scroll = QScrollArea()
        scroll.setWidgetResizable(True)
        scroll.setStyleSheet("QScrollArea { border: none; background-color: transparent; }")
        
        scroll_content = QWidget()
        scroll_layout = QVBoxLayout(scroll_content)
        scroll_layout.setAlignment(Qt.AlignmentFlag.AlignTop)
        
        # Mock Download Item
        dl_frame = QFrame()
        dl_frame.setStyleSheet("background-color: #1e1f22; border-radius: 8px; padding: 15px;")
        dl_layout = QVBoxLayout(dl_frame)
        
        info_row = QHBoxLayout()
        title = QLabel("Void Runners - High Res Texture Pack")
        title.setStyleSheet("font-weight: bold; font-size: 16px;")
        speed = QLabel("14.5 MB/s  |  Network")
        speed.setStyleSheet("color: #00c853; font-weight: bold;")
        
        info_row.addWidget(title)
        info_row.addStretch()
        info_row.addWidget(speed)
        dl_layout.addLayout(info_row)
        
        prog_bar = QProgressBar()
        prog_bar.setValue(45)
        prog_bar.setFixedHeight(15)
        dl_layout.addWidget(prog_bar)
        
        status_row = QHBoxLayout()
        size_lbl = QLabel("1.2 GB / 2.8 GB")
        size_lbl.setStyleSheet("color: #b5bac1;")
        
        btn_pause = QPushButton("⏸ Pause")
        btn_pause.setStyleSheet("background-color: #2b2d31; padding: 6px 12px; border-radius: 4px;")
        btn_cancel = QPushButton("⏹ Cancel")
        btn_cancel.setStyleSheet("background-color: #ff5252; color: white; padding: 6px 12px; border-radius: 4px;")
        
        status_row.addWidget(size_lbl)
        status_row.addStretch()
        status_row.addWidget(btn_pause)
        status_row.addWidget(btn_cancel)
        dl_layout.addLayout(status_row)
        
        scroll_layout.addWidget(dl_frame)
        scroll.setWidget(scroll_content)
        layout.addWidget(scroll)
        self.page_stack.addWidget(page)

    # --- SETTINGS PAGE ---
    def init_settings_page(self):
        page = QWidget()
        layout = QVBoxLayout(page)
        layout.setContentsMargins(30, 30, 30, 30)
        layout.setSpacing(15)
        layout.setAlignment(Qt.AlignmentFlag.AlignTop)
        
        title = QLabel("Settings")
        title.setStyleSheet("font-size: 24px; font-weight: bold; color: white;")
        layout.addWidget(title)
        
        dir_label = QLabel("Global Installation Directory")
        dir_label.setStyleSheet("font-size: 14px; color: #b5bac1; font-weight: bold;")
        layout.addWidget(dir_label)
        
        dir_row = QHBoxLayout()
        self.dir_input = QLineEdit(self.download_directory)
        self.dir_input.setReadOnly(True) 
        
        btn_browse = QPushButton("Change Folder...")
        btn_browse.setStyleSheet("background-color: #2b2d31; color: white; border: none; padding: 6px 12px; border-radius: 4px;")
        btn_browse.clicked.connect(self.browse_download_directory)
        
        dir_row.addWidget(self.dir_input)
        dir_row.addWidget(btn_browse)
        layout.addLayout(dir_row)
        
        self.page_stack.addWidget(page)

    def browse_download_directory(self):
        selected_dir = QFileDialog.getExistingDirectory(self, "Select Global Installation Directory", self.download_directory)
        if selected_dir:
            self.download_directory = selected_dir
            self.dir_input.setText(selected_dir)
            
            # Refresh library detail view if open
            current_item = self.lib_list.currentItem()
            if current_item:
                self.update_library_detail(current_item, None)

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = LauncherApp()
    window.show()
    sys.exit(app.exec())