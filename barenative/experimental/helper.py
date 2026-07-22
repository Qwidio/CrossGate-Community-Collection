# this one is the main processor of everything

import sys
import os
import platform
import uuid
import socket
import logging
import threading
from pathlib import Path
from flask import Flask, request, jsonify
import requests
import pystray
from PIL import Image, ImageDraw
import time 
import subprocess 

GLOBAL_PORT = None
last_ui_tick = 0.0

logging.basicConfig(
    filename='launcher_helper.log',
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

app = Flask(__name__)
API_URL_AUTH = "http://localhost/crossgate/crossgate-community-collection/api/auth.php"
API_URL_REAUTH = "http://localhost/crossgate/crossgate-community-collection/api/reauth.php"
API_KEY = "3a0741edc4b7725d2473e2f9e887eba2.9310aef665af94f5e6f40ac3f71708ba03c6231c9705ac530dda434ab2b951b0"

# Used to communicate tray clicks to the launcher
pending_ui_command = {"cmd": None}

def get_device_id():
    DEVICE_FILE = Path("device.id")
    if DEVICE_FILE.exists():
        return DEVICE_FILE.read_text()
    DEVICE_FILE.write_text(device_id)
    device_id = str(uuid.uuid4())
    return device_id

@app.route("/login", methods=["POST"])
def login():
    try:
        launcher_data = request.get_json()
        username = launcher_data.get("username")
        password = launcher_data.get("password")
        sessionless = launcher_data.get("sessionless")

        payload = {
            "username": username,
            "password": password,
            "os": platform.system(),
            "address": get_device_id(),
            "sessionless": sessionless
        }
        headers = {
            "X-Api-Key": API_KEY,
            "Content-Type": "application/json"
        }
        
        logging.info(f"Attempting login for {username} to the API")
        response = requests.post(API_URL_AUTH, json=payload, headers=headers, timeout=15)
        response.raise_for_status()
        api_data = response.json()

        PROFILE_FIELDS = {
            "name": "profileNames", "bio": "profileBios", "joinDate": "profileJDates",
            "tags": "profileTags", "avatar": "profileAttachs", "badge": "profileBadge"
        }
                
        profile_data = {
            key: api_data.get(source) for key, source in PROFILE_FIELDS.items()
        }

        processed = {
            "success": api_data.get("message") == "Login Successful",
            "message": api_data.get("message"),
            "profileData": profile_data,
            "activityState": api_data.get("activityState"),
            "profileMarkOut": api_data.get("profileMarkOut")
        }
        
        if not sessionless:
            processed["sessionToken"] = api_data.get("sessionToken")
            processed["expireDate"] = api_data.get("unixexpdate")

        return jsonify(processed)

    except requests.RequestException as e:
        logging.error(f"API Error: {str(e)}")
        return jsonify({"success": False, "error": f"API Error: {str(e)}"}), 500
    except Exception as e:
        logging.error(f"Exception: {str(e)}")
        return jsonify({"success": False, "error": str(e)}), 500
    

@app.route("/getcollection", methods=["GET"])
def getcollection():
    try:
        launcher_data = request.get_json()
        payload = {
            "mkot": launcher_data.get("mkot"),
            "tokens": launcher_data.get("tokens"),
            "os": platform.system(),
            "address": get_device_id()
        }
        headers = {
            "X-Api-Key": API_KEY,
            "Content-Type": "application/json"
        }
        
        logging.info(f"Attempting to retrieve collection data")
        response = requests.post(API_URL_AUTH, json=payload, headers=headers, timeout=15)
        response.raise_for_status()
        api_data = response.json()

        COLLECTION_RETURN = {
            "id": "libsIds", "title": "libsTitle", "desc": "libsDesc", "status": "status", 
            "icon_text": "clt", "icon_image": "libsAttachs", "fileRef": "fdrLibs",
            "banner": "libsBanner"
        }
                
        collection_data = {
            key: api_data.get(source) for key, source in COLLECTION_RETURN.items()
        }
        if not api_data.get("banner_color"):
            collection_data["banner_color"] = "#6c4fd6"

        return jsonify(collection_data)

    except requests.RequestException as e:
        logging.error(f"Failed to Retrieve Collection: {str(e)}")
        return jsonify({"success": False, "error": str(e)}), 500
    except Exception as e:
        logging.error(f"Exception: {str(e)}")
        return jsonify({"success": False, "error": str(e)}), 500

@app.route("/verifysession", methods=["PUT"])
def verify_session():
    try:
        launcher_data = request.get_json()
        payload = {
            "tokens": launcher_data.get("tokens"),
            "address": get_device_id(),
            "os": platform.system()
        }
        headers = {
            "X-Api-Key": API_KEY, "Content-Type": "application/json"
        }
        
        logging.info("Verifying session token with API.")
        response = requests.put(API_URL_REAUTH, json=payload, headers=headers, timeout=15)
        response.raise_for_status()
        
        api_data = response.json()
        success = api_data.get("message") == "Session Valid"
        return jsonify({"success": success, "data": api_data})
        
    except Exception as e:
        logging.error(f"Session verification failed: {e}")
        return jsonify({"success": False, "error": str(e)}), 500

@app.route("/get_command", methods=["GET"])
def get_command():
    global last_ui_tick
    last_ui_tick = time.time()
    
    cmd = pending_ui_command["cmd"]
    pending_ui_command["cmd"] = None
    return jsonify({"status": "running", "command": cmd})

def set_ui_command(cmd):
    global last_ui_tick
    logging.info(f"Tray clicked: {cmd}")
    
    # default 1s
    if time.time() - last_ui_tick > 1.0:
        logging.info("Launcher is closed. Respawning directly...")
        subprocess.Popen([sys.executable, "client.py", str(GLOBAL_PORT), cmd])
    else:
        pending_ui_command["cmd"] = cmd

def create_tray_icon():
    image = Image.new('RGB', (64, 64), color=(43, 45, 49))
    d = ImageDraw.Draw(image)
    d.text((10,25), "CGCC", fill=(255,255,255))
    
    def on_exit(icon, item):
        logging.info("Executing Tray Exit. Hard stopping processes.")
        icon.stop()
        threading.Timer(0.2, lambda: os._exit(0)).start()

    menu = (
        pystray.MenuItem('Open Launcher', lambda: set_ui_command("HOME")),
        pystray.MenuItem('Library', lambda: set_ui_command("LIBRARY")),
        pystray.MenuItem('Downloads', lambda: set_ui_command("DOWNLOADS")),
        pystray.MenuItem('Settings', lambda: set_ui_command("SETTINGS")),
        pystray.MenuItem('Exit', on_exit)
    )
    icon = pystray.Icon("Launcher", image, "Background Helper", menu)
    icon.run()

def find_free_port():
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.bind(('', 0))
        return s.getsockname()[1]

if __name__ == "__main__":
    port = find_free_port()
    GLOBAL_PORT = port
    print(f"HELPER_PORT={port}", flush=True) 
    
    tray_thread = threading.Thread(target=create_tray_icon, daemon=True)
    tray_thread.start()
    
    app.run(host="127.0.0.1", port=port, debug=False, use_reloader=False)