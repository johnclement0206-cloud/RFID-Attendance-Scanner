from flask import Flask, request, jsonify
import pygame
import numpy as np

app = Flask(__name__)

# Initialize pygame mixer
pygame.mixer.init()

# Create sounds
def create_success_sound():
    sample_rate = 22050
    duration = 0.3
    frequency = 1000
    
    t = np.linspace(0, duration, int(sample_rate * duration))
    wave = np.sin(2 * np.pi * frequency * t)
    fade = np.linspace(1, 0, len(wave))
    wave = wave * fade
    wave = (wave * 32767).astype(np.int16)
    stereo_wave = np.column_stack((wave, wave))
    
    return pygame.sndarray.make_sound(stereo_wave)

def create_error_sound():
    sample_rate = 22050
    duration = 0.2
    frequency = 400
    
    t = np.linspace(0, duration, int(sample_rate * duration))
    wave = np.sin(2 * np.pi * frequency * t)
    fade = np.linspace(1, 0, len(wave))
    wave = wave * fade
    wave = (wave * 32767).astype(np.int16)
    stereo_wave = np.column_stack((wave, wave))
    
    return pygame.sndarray.make_sound(stereo_wave)

def create_scan_sound():
    sample_rate = 22050
    duration = 0.1
    frequency = 800
    
    t = np.linspace(0, duration, int(sample_rate * duration))
    wave = np.sin(2 * np.pi * frequency * t)
    fade = np.linspace(1, 0, len(wave))
    wave = wave * fade
    wave = (wave * 32767).astype(np.int16)
    stereo_wave = np.column_stack((wave, wave))
    
    return pygame.sndarray.make_sound(stereo_wave)

# Generate sounds
success_sound = create_success_sound()
error_sound = create_error_sound()
scan_sound = create_scan_sound()

@app.route('/sound/scan', methods=['POST'])
def play_scan():
    scan_sound.play()
    return jsonify({"status": "played"}), 200

@app.route('/sound/success', methods=['POST'])
def play_success():
    success_sound.play()
    return jsonify({"status": "played"}), 200

@app.route('/sound/error', methods=['POST'])
def play_error():
    error_sound.play()
    return jsonify({"status": "played"}), 200

if __name__ == '__main__':
    print("Audio Feedback Server Running")
    print("Listening on http://localhost:5000")
    app.run(host='0.0.0.0', port=5000, debug=False)
