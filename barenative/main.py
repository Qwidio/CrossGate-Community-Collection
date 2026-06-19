# note: the code is AI assisted but always goes through my own modification since it still bad at doing UI than me despite my lack of Python experience

import sys
import os
from PyQt6.QtWidgets import (
    QApplication, QMainWindow, QWidget, QHBoxLayout, QVBoxLayout, 
    QPushButton, QLabel, QStackedWidget, QListWidget, QListWidgetItem,
    QFileDialog, QLineEdit, QFrame, QSplitter
)
from PyQt6.QtCore import Qt

# Mock Database including banner colors (as placeholders for banner images)
MOCK_APPS = [
    {
        "id": "NIE", 
        "title": "NamelessInExistence", 
        "desc": "Within the nothingness. everything that can, will exist",
        "status": "Ready to Play", 
        "banner_color": "#4f83d6",
        "icon_text": "NIE"
    },
]

OPTIONS = [
    {
        "id": "1", 
        "title": "Library", 
        "icon_img": "lbr.svg",
        "icon_text": "LBR"
    },
    {
        "id": "2", 
        "title": "Downloads", 
        "icon_img": "dl.svg", 
        "icon_text": "DL"
    },
    {
        "id": "3", 
        "title": "Settings", 
        "icon_img": "stg.svg",
        "icon_text": "STG"
    },
]

class LauncherApp(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("App Launcher")
        self.resize(1366, 768)
        self.download_directory = os.path.expanduser("~/Downloads") # Default Path
        
        # Base Dark Mode Theme
        self.setStyleSheet("""
            QMainWindow { background-color: #111213; }
            QWidget { color: #e3e3e3; font-family: 'Segoe UI', sans-serif; }
            QLineEdit { background-color: #1e1f22; border: 1px solid #2b2d31; border-radius: 4px; padding: 6px; color: white; }
            QListWidget { background-color: transparent; border: none; }
        """)

        # Main horizontal layout structure
        main_layout = QHBoxLayout()
        main_layout.setContentsMargins(0, 0, 0, 0)
        main_layout.setSpacing(0)

        # -------------------------------------------------------------
        # 1. LEFTMOST SQUARE ICON BAR (Apps)
        # -------------------------------------------------------------
        self.icon_bar = QWidget()
        self.icon_bar.setFixedWidth(65)
        self.icon_bar.setStyleSheet("background-color: #1e1f22; border-right: 1px solid #2b2d31;")
        icon_layout = QVBoxLayout(self.icon_bar)
        icon_layout.setContentsMargins(5, 15, 5, 15)
        icon_layout.setSpacing(10)
        icon_layout.setAlignment(Qt.AlignmentFlag.AlignTop)

        # Generate square buttons for each app
        for app_data in MOCK_APPS:
            btn = QPushButton(app_data["icon_text"])
            btn.setFixedSize(55, 55)
            # You can add background-image here later just like the options bar
            btn.setStyleSheet(f"""
                QPushButton {{ 
                    background-color: {app_data['banner_color']}; color: white;
                    font-weight: bold; border-radius: 8px; font-size: 14px; border: 2px solid #2b2d31;
                }}
                QPushButton:hover {{ border: 2px solid #5865f2; }}
            """)
            # Clicking an icon forces the main stack to "Home" and updates the background
            btn.clicked.connect(lambda checked, data=app_data: self.switch_to_home_app(data))
            icon_layout.addWidget(btn)

        # PAGES (QStackedWidget)
        self.page_stack = QStackedWidget()
        
        self.init_home_page()       # Index 0
        self.init_library_page()    # Index 1
        self.init_downloads_page()  # Index 2
        self.init_settings_page()   # Index 3

        # Vertical navbar
        self.options_bar = QWidget()
        self.options_bar.setFixedWidth(40)
        self.options_bar.setStyleSheet("background-color: #ffffff; border-left: 1px solid #ffffff;")
        options_layout = QVBoxLayout(self.options_bar)
        options_layout.setContentsMargins(5, 15, 5, 15)
        options_layout.setSpacing(10)
        options_layout.setAlignment(Qt.AlignmentFlag.AlignTop)

        # Map options to their respective page stack indices (Library=1, Downloads=2, Settings=3)
        for index, opt_data in enumerate(OPTIONS):
            btn = QPushButton("")
            btn.setFixedSize(30, 30)
            
            # Use border-image to stretch the png over the button. 
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

        # -------------------------------------------------------------
        # ASSEMBLE LAYOUT (Left to Right)
        # -------------------------------------------------------------
        main_layout.addWidget(self.icon_bar)      # Left
        main_layout.addWidget(self.page_stack)    # Center
        main_layout.addWidget(self.options_bar)   # Right

        central_widget = QWidget()
        central_widget.setLayout(main_layout)
        self.setCentralWidget(central_widget)
        
        # Load the first app on initialization
        if MOCK_APPS:
            self.switch_to_home_app(MOCK_APPS[0])

    # PAGE HOME PANEL
    def init_home_page(self):
        self.home_page = QFrame()
        layout = QVBoxLayout(self.home_page)
        layout.setContentsMargins(30, 40, 30, 40)
        
        # App details (Top Left)
        self.home_title = QLabel("Game Title")
        self.home_title.setStyleSheet("font-size: 32px; font-weight: bold; color: white;")
        layout.addWidget(self.home_title)
        
        self.home_desc = QLabel("Game Description goes here.")
        self.home_desc.setWordWrap(True)
        self.home_desc.setFixedWidth(400)
        self.home_desc.setStyleSheet("font-size: 14px; color: white; margin-top: 10px;")
        layout.addWidget(self.home_desc)
        
        layout.addStretch() # Push launch button to the bottom
        
        # Launch Button container (Bottom Left)
        launch_layout = QHBoxLayout()
        self.btn_launch = QPushButton("LAUNCH")
        self.btn_launch.setFixedSize(220, 60)
        self.btn_launch.setStyleSheet("""
            QPushButton {
                background-color: #ffc107; color: #111213; border: none;
                font-size: 20px; font-weight: bold; border-radius: 6px;
            }
            QPushButton:hover { background-color: #ffe066; }
        """)
        self.btn_launch.clicked.connect(self.on_launch_clicked)
        launch_layout.addWidget(self.btn_launch)
        launch_layout.addStretch()
        
        layout.addLayout(launch_layout)
        self.page_stack.addWidget(self.home_page)

    def switch_to_home_app(self, app_data):
        self.current_home_app = app_data
        self.home_title.setText(app_data["title"])
        self.home_desc.setText(app_data["desc"])
        
        if app_data["status"] == "Ready to Play":
            self.btn_launch.setText("LAUNCH")
            self.btn_launch.setStyleSheet("QPushButton { background-color: #00c853; color: black; font-size: 20px; font-weight: bold; border-radius: 6px; border: none; } QPushButton:hover { background-color: #2edb69; }")
        elif app_data["status"] == "Update Available":
            self.btn_launch.setText("UPDATE")
            self.btn_launch.setStyleSheet("QPushButton { background-color: #ff9100; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; border: none; } QPushButton:hover { background-color: #ffa726; }")
        else:
            self.btn_launch.setText("DOWNLOAD")
            self.btn_launch.setStyleSheet("QPushButton { background-color: #2979ff; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; border: none; } QPushButton:hover { background-color: #60a5fa; }")

        self.home_page.setStyleSheet(f"background-color: {app_data['banner_color']}; border-radius: 0px;")
        self.page_stack.setCurrentIndex(0)

    def on_launch_clicked(self):
        print(f"Action triggered from Home for: {self.current_home_app['title']} -> State: {self.btn_launch.text()}")

    # PAGE LIBRARY PANEL (Minimalist + Split Detail Panel)
    def init_library_page(self):
        page = QWidget()
        layout = QHBoxLayout(page)
        layout.setContentsMargins(0,0,0,0)
        layout.setSpacing(0)
        
        splitter = QSplitter(Qt.Orientation.Horizontal)
        splitter.setStyleSheet("QSplitter::handle { background-color: #2b2d31; width: 1px; }")
        
        self.lib_list = QListWidget()
        self.lib_list.setStyleSheet("""
            QListWidget::item { 
                padding: 15px; color: #b5bac1; border-bottom: 1px solid #1e1f22; 
            }
            QListWidget::item:selected { 
                background-color: #2b2d31; color: white; font-weight: bold; 
            }
            QListWidget::item:hover { background-color: #1e1f22; }
        """)
        
        for app in MOCK_APPS:
            item = QListWidgetItem(app["title"])
            item.setData(Qt.ItemDataRole.UserRole, app)
            self.lib_list.addItem(item)
            
        self.lib_list.currentItemChanged.connect(self.update_library_detail_panel)
        splitter.addWidget(self.lib_list)
        
        self.detail_panel = QFrame()
        self.detail_panel.setStyleSheet("background-color: #1e1f22; padding: 20px;")
        self.detail_layout = QVBoxLayout(self.detail_panel)
        self.detail_layout.setAlignment(Qt.AlignmentFlag.AlignTop)
        
        self.detail_title = QLabel("Select an app")
        self.detail_title.setStyleSheet("font-size: 22px; font-weight: bold; color: white;")
        self.detail_layout.addWidget(self.detail_title)
        
        self.detail_status = QLabel("")
        self.detail_status.setStyleSheet("color: #b5bac1; font-size: 14px; margin-top: 5px;")
        self.detail_layout.addWidget(self.detail_status)
        
        self.detail_action_btn = QPushButton("Action")
        self.detail_action_btn.setFixedWidth(150)
        self.detail_action_btn.setVisible(False)
        self.detail_layout.addWidget(self.detail_action_btn)
        
        splitter.addWidget(self.detail_panel)
        splitter.setSizes([400, 600])
        
        layout.addWidget(splitter)
        self.page_stack.addWidget(page)
        
        if self.lib_list.count() > 0:
            self.lib_list.setCurrentRow(0)

    def update_library_detail_panel(self, current, previous):
        if not current:
            return
        app_data = current.data(Qt.ItemDataRole.UserRole)
        
        self.detail_title.setText(app_data["title"])
        self.detail_status.setText(f"Status: {app_data['status']}\n\nInstall Path Reference:\n{self.download_directory}/{app_data['title']}")
        self.detail_action_btn.setVisible(True)
        self.detail_action_btn.setText(app_data["status"])
        
        base_btn_style = "border: none; padding: 8px 16px; border-radius: 4px;"
        if app_data["status"] == "Ready to Play":
            self.detail_action_btn.setStyleSheet(f"background-color: #00c853; color: black; font-weight: bold; {base_btn_style}")
        elif app_data["status"] == "Update Available":
            self.detail_action_btn.setStyleSheet(f"background-color: #ff9100; color: white; font-weight: bold; {base_btn_style}")
        else:
            self.detail_action_btn.setStyleSheet(f"background-color: #2979ff; color: white; font-weight: bold; {base_btn_style}")

    # PAGE DOWNLOADS PANEL
    def init_downloads_page(self):
        page = QWidget()
        layout = QVBoxLayout(page)
        layout.setContentsMargins(30, 30, 30, 30)
        
        title = QLabel("Downloads")
        title.setStyleSheet("font-size: 24px; font-weight: bold; color: white;")
        layout.addWidget(title)
        
        layout.addWidget(QLabel("No Active Downloads running in queue."))
        layout.addStretch()
        self.page_stack.addWidget(page)

    # PAGE SETTINGS PANEL
    def init_settings_page(self):
        page = QWidget()
        layout = QVBoxLayout(page)
        layout.setContentsMargins(30, 30, 30, 30)
        layout.setSpacing(15)
        layout.setAlignment(Qt.AlignmentFlag.AlignTop)
        
        title = QLabel("Settings")
        title.setStyleSheet("font-size: 24px; font-weight: bold; color: white;")
        layout.addWidget(title)
        
        dir_label = QLabel("Downloads Installation Directory")
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
        selected_dir = QFileDialog.getExistingDirectory(
            self, 
            "Select Global Installation Directory", 
            self.download_directory
        )
        if selected_dir:
            self.download_directory = selected_dir
            self.dir_input.setText(selected_dir)
            
            current_item = self.lib_list.currentItem()
            if current_item:
                self.update_library_detail_panel(current_item, None)


if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = LauncherApp()
    window.show()
    sys.exit(app.exec())