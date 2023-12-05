import time
import pygetwindow as gw
import csv
import pandas as pd
import json

def get_focused_window():
    try:
        window = gw.getWindowsWithTitle(gw.getActiveWindow().title)
        return window[0].title if window else None
    except IndexError:
        return None

def save_to_csv(data):
    with open('application_usage.csv', 'a', newline='', encoding='utf-8') as csvfile:
        fieldnames = ['Application', 'Duration']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)

        # Write header if the file is empty
        if csvfile.tell() == 0:
            writer.writeheader()

        writer.writerow(data)

def read_csv():
    df = pd.read_csv('application_usage.csv')
    return df.to_dict(orient='records')

def save_to_json(data):
    with open('application_usage.json', 'w') as jsonfile:
        json.dump(data, jsonfile)

def track_application_usage():
    previous_window = None
    start_time = time.time()

    while True:
        current_window = get_focused_window()

        if current_window != previous_window:
            # Calculate duration and save data to CSV
            if previous_window:
                duration = time.time() - start_time
                data = {'Application': previous_window, 'Duration': duration}
                save_to_csv(data)
                # Save data to JSON file for DataTables
                all_data = read_csv()
                save_to_json(all_data)

            # Reset timer for the new window
            start_time = time.time()
            previous_window = current_window

        time.sleep(1)

if __name__ == "__main__":
    import threading

    # Start application usage tracking in a separate thread
    tracking_thread = threading.Thread(target=track_application_usage)
    tracking_thread.start()
