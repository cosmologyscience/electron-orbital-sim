<!DOCTYPE html>
<html>
<head>
    <title>Electron Orbital Simulation - Full Screen</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #002222;
        }
        canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
        }
        .controls {
            position: fixed;
            top: 10px;
            left: 10px;
            color: white;
            background: rgba(0,0,0,0.7);
            padding: 20px;
            border-radius: 5px;
            z-index: 1000;
        }
        .controls-bottom {
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0,0,0,0.7);
            padding: 15px;
            border-radius: 5px;
            width: max-content;
            z-index: 1000;
        }
        .nav-buttons {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        .nav-button {
            text-decoration: none;
            color: white;
            background-color: #003333;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .nav-button:hover {
            background-color: #004444;
        }
        .sliders-container {
            display: flex;
            gap: 20px;
        }
        .slider-container {
            flex: 1;
        }
        .slider-container label {
            display: block;
            margin-bottom: 5px;
        }
        .slider-container input {
            width: 100%;
        }
        .value-display {
            display: inline-block;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="controls">
        <div class="instructions">Press ESC to pause/resume</div>
    </div>

    <div class="nav-buttons">
        <a href="https://awpodcast.com/cosmology/r/atom" class="nav-button">Normal View</a>
        <a href="https://awpodcast.com/cosmology/r/atom/full_screen.php" class="nav-button">Full Screen</a>
        <a href="https://awpodcast.com/cosmology/r/atom/two_electrons.php" class="nav-button">Two Electrons</a>
    </div>

    <canvas id="waterCanvas"></canvas>

    <div class="controls-bottom">
        <div class="sliders-container">
            <div class="slider-container">
                <label for="orbitalRadius">Orbital Radius</label>
                <input type="range" id="orbitalRadius" min="50" max="500" value="150" step="10">
                <span class="value-display" id="orbitalRadiusValue">150</span>
            </div>
            <div class="slider-container">
                <label for="orbitalSpeed">Orbital Speed</label>
                <input type="range" id="orbitalSpeed" min="0.5" max="5" value="2" step="0.1">
                <span class="value-display" id="orbitalSpeedValue">2</span>
            </div>
            <div class="slider-container">
                <label for="spinSpeed">Spin Speed</label>
                <input type="range" id="spinSpeed" min="0.01" max="0.5" value="0.15" step="0.01">
                <span class="value-display" id="spinSpeedValue">0.15</span>
            </div>
            <div class="slider-container">
                <label for="rippleLength">Ripple Length</label>
                <input type="range" id="rippleLength" min="100" max="500" value="200" step="10">
                <span class="value-display" id="rippleLengthValue">200</span>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('waterCanvas');
        const ctx = canvas.getContext('2d');

        // Get slider elements
        const orbitalRadiusSlider = document.getElementById('orbitalRadius');
        const orbitalSpeedSlider = document.getElementById('orbitalSpeed');
        const spinSpeedSlider = document.getElementById('spinSpeed');
        const rippleLengthSlider = document.getElementById('rippleLength');
        const orbitalRadiusValue = document.getElementById('orbitalRadiusValue');
        const orbitalSpeedValue = document.getElementById('orbitalSpeedValue');
        const spinSpeedValue = document.getElementById('spinSpeedValue');
        const rippleLengthValue = document.getElementById('rippleLengthValue');

        let isPaused = false;

        // Make canvas full screen and handle resize
        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            center.x = canvas.width / 2;
            center.y = canvas.height / 2;
        }

        window.addEventListener('resize', resizeCanvas);

        // Center point (proton)
        const center = {
            x: window.innerWidth / 2,
            y: window.innerHeight / 2
        };

        // Initial canvas setup
        resizeCanvas();

        // Electron properties
        let electron = {
            x: center.x + 150,
            y: center.y,
            rotation: 0,
            orbitalAngle: 0,
            size: 5
        };

        // Ripples array
        let ripples = [];

        // Update slider value displays
        orbitalRadiusSlider.addEventListener('input', (e) => {
            orbitalRadiusValue.textContent = e.target.value;
        });

        orbitalSpeedSlider.addEventListener('input', (e) => {
            orbitalSpeedValue.textContent = e.target.value;
        });

        spinSpeedSlider.addEventListener('input', (e) => {
            spinSpeedValue.textContent = e.target.value;
        });

        rippleLengthSlider.addEventListener('input', (e) => {
            rippleLengthValue.textContent = e.target.value;
        });

        // Add ESC key handler
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                isPaused = !isPaused;
            }
        });

        function createRipple(x, y, angle) {
            ripples.push({
                x,
                y,
                radius: 1,
                angle: angle,
                opacity: 1,
                maxRadius: parseInt(rippleLengthSlider.value)
            });
        }

        function drawProton() {
            ctx.beginPath();
            ctx.arc(center.x, center.y, 2, 0, Math.PI * 2);
            ctx.fillStyle = '#ff6666';
            ctx.fill();
            ctx.strokeStyle = '#ff0000';
            ctx.lineWidth = 2;
            ctx.stroke();
        }

        function drawElectron(x, y, rotation) {
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(rotation);
            
            // Draw electron with visible spin markers
            ctx.beginPath();
            ctx.arc(0, 0, 10, 0, Math.PI * 2);
            ctx.fillStyle = '#6666ff';
            ctx.fill();
            
            // Add visible spin markers
            ctx.beginPath();
            ctx.moveTo(-8, -8);
            ctx.lineTo(8, 8);
            ctx.moveTo(8, -8);
            ctx.lineTo(-8, 8);
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();
            
            ctx.restore();
        }

        function updateElectron() {
            if (isPaused) return;

            const orbitalRadius = parseInt(orbitalRadiusSlider.value);
            const orbitalSpeed = parseFloat(orbitalSpeedSlider.value);
            const spinSpeed = parseFloat(spinSpeedSlider.value);
            
            // Update orbital position
            electron.orbitalAngle += orbitalSpeed * 0.02;
            electron.x = center.x + Math.cos(electron.orbitalAngle) * orbitalRadius;
            electron.y = center.y + Math.sin(electron.orbitalAngle) * orbitalRadius;
            
            // Update electron spin
            electron.rotation += spinSpeed;

            // Create new ripples
            for (let i = 0; i < 4; i++) {
                const angle = electron.rotation + (Math.PI / 2 * i);
                createRipple(
                    electron.x + Math.cos(angle) * 10,
                    electron.y + Math.sin(angle) * 10,
                    angle
                );
            }
        }

        function updateRipples() {
            if (isPaused) return;

            for (let i = ripples.length - 1; i >= 0; i--) {
                const ripple = ripples[i];
                ripple.radius += 2;
                ripple.opacity = Math.max(0, 1 - (ripple.radius / ripple.maxRadius));
                
                if (ripple.opacity <= 0) {
                    ripples.splice(i, 1);
                }
            }
        }

        function drawRipples() {
            ripples.forEach(ripple => {
                ctx.beginPath();
                ctx.arc(ripple.x, ripple.y, ripple.radius, 0, Math.PI * 2);
                ctx.strokeStyle = `rgba(100, 100, 255, ${ripple.opacity})`;
                ctx.lineWidth = 2;
                ctx.stroke();

                // Add spiral effect
                ctx.beginPath();
                ctx.arc(
                    ripple.x + Math.cos(ripple.angle) * ripple.radius * 0.5,
                    ripple.y + Math.sin(ripple.angle) * ripple.radius * 0.5,
                    ripple.radius * 0.3,
                    ripple.angle,
                    ripple.angle + Math.PI * 1.5
                );
                ctx.strokeStyle = `rgba(150, 150, 255, ${ripple.opacity * 0.7})`;
                ctx.stroke();
            });
        }

        function drawOrbitalPath() {
            const orbitalRadius = parseInt(orbitalRadiusSlider.value);
            ctx.beginPath();
            ctx.arc(center.x, center.y, orbitalRadius, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 1;
            ctx.stroke();
        }

        function animate() {
            ctx.fillStyle = '#002222';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            drawOrbitalPath();
            updateElectron();
            updateRipples();
            drawRipples();
            drawProton();
            drawElectron(electron.x, electron.y, electron.rotation);

            requestAnimationFrame(animate);
        }

        animate();
    </script>
</body>
</html>
