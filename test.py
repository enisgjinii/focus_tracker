import time
import pygetwindow as gw
import csv
import pandas as pd
import json
import threading
import logging
from datetime import datetime

logging.basicConfig(
    filename="application_usage.log",
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s: %(message)s",
)


def get_focused_window():
    try:
        window = gw.getWindowsWithTitle(gw.getActiveWindow().title)
        return window[0].title if window else None
    except IndexError:
        return None


def save_to_csv(data):
    try:
        with open(
            "application_usage.csv", "a", newline="", encoding="utf-8"
        ) as csvfile:
            fieldnames = ["Application", "Duration", "Date"]
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)

            # Write header if the file is empty
            if csvfile.tell() == 0:
                writer.writeheader()

            writer.writerow(data)
    except Exception as e:
        logging.error(f"Error saving to CSV: {e}")


def read_csv():
    try:
        df = pd.read_csv("application_usage.csv")
        return df.to_dict(orient="records")
    except Exception as e:
        logging.error(f"Error reading CSV: {e}")
        return []


def save_to_json(data):
    try:
        with open("application_usage.json", "w") as jsonfile:
            json.dump(data, jsonfile)
    except Exception as e:
        logging.error(f"Error saving to JSON: {e}")


def track_application_usage():
    previous_window = None
    start_time = time.time()

    try:
        while True:
            current_window = get_focused_window()

            if current_window != previous_window:
                # Calculate duration and save data to CSV
                if previous_window:
                    duration = time.time() - start_time
                    current_date = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    data = {
                        "Application": previous_window,
                        "Duration": duration,
                        "Date": current_date,
                    }
                    save_to_csv(data)
                    # Save data to JSON file for DataTables
                    all_data = read_csv()
                    save_to_json(all_data)

                # Reset timer for the new window
                start_time = time.time()
                previous_window = current_window

            time.sleep(1)
    except Exception as e:
        logging.error(f"Error in tracking application usage: {e}")


if __name__ == "__main__":
    try:
        # Start application usage tracking in a separate thread
        tracking_thread = threading.Thread(target=track_application_usage)
        tracking_thread.start()
    except Exception as e:
        logging.error(f"Error starting tracking thread: {e}")
