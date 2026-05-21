import serial, csv, os, time, json
from datetime import datetime

PORT = 'COM5'
BAUD = 115200
CSV_FILE = 'curl_data.csv'
THRESH_UP = 1.5
THRESH_DOWN = -1.5

print('Connecting to ' + PORT)
ser = serial.Serial(PORT, BAUD, timeout=2)
time.sleep(2)
print('Connected! Saving to: ' + os.path.abspath(CSV_FILE))
print('Press Ctrl+C to stop.')

file_exists = os.path.isfile(CSV_FILE)
csvfile = open(CSV_FILE, 'a', newline='')
writer = csv.writer(csvfile)
if not file_exists:
    writer.writerow(['timestamp','ax','ay','az','gx','gy','gz','temp','rep','phase'])

phase = 'rest'
rep = 0
cooldown = 0

def detect_rep(ay):
    global phase, rep, cooldown
    if cooldown > 0:
        cooldown -= 1
        return phase
    if phase in ('rest','down'):
        if ay > THRESH_UP:
            phase = 'up'
            cooldown = 5
    elif phase == 'up':
        if ay < THRESH_DOWN:
            phase = 'down'
            rep += 1
            cooldown = 8
            print('REP ' + str(rep) + '  ay=' + str(round(ay,3)))
    if abs(ay) < 0.3 and phase == 'down':
        phase = 'rest'
    return phase

try:
    while True:
        raw = ser.readline().decode('utf-8', errors='ignore').strip()
        if not raw or not raw.startswith('{'):
            continue
        try:
            d = json.loads(raw)
        except:
            continue
        if 'ax' not in d:
            continue
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        phase = detect_rep(d['ay'])
        writer.writerow([now, round(d['ax'],4), round(d['ay'],4), round(d['az'],4),
                         round(d['gx'],4), round(d['gy'],4), round(d['gz'],4),
                         round(d['temp'],2), rep, phase])
        csvfile.flush()
        print('ay=' + str(round(d['ay'],3)) + '  phase=' + phase + '  rep=' + str(rep), end='\r')
except KeyboardInterrupt:
    print('Stopped. Total reps: ' + str(rep))
    csvfile.close()
    ser.close()
