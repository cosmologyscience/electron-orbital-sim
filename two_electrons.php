<!DOCTYPE html>
<html>
<head>
    <title>Two-Electron Orbital Simulation</title>
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
            flex-wrap: wrap;
        }
        .electron-controls {
            display: flex;
            gap: 20px;
            padding: 10px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .slider-container {
            flex: 1;
            min-width: 150px;
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
            <div class="electron-controls">
                <h3 style="margin: 0 0 10px 0; color: #6666ff;">Electron 1</h3>
                <div class="slider-container">
                    <label for="orbitalRadius1">Orbital Radius</label>
                    <input type="range" id="orbitalRadius1" min="50" max="500" value="150" step="10">
                    <span class="value-display" id="orbitalRadius1Value">150</span>
                </div>
                <div class="slider-container">
                    <label for="orbitalSpeed1">Orbital Speed</label>
                    <input type="range" id="orbitalSpeed1" min="0.5" max="5" value="2" step="0.1">
                    <span class="value-display" id="orbitalSpeed1Value">2</span>
                </div>
                <div class="slider-container">
                    <label for="spinSpeed1">Spin Speed</label>
                    <input type="range" id="spinSpeed1" min="0.01" max="0.5" value="0.15" step="0.01">
                    <span class="value-display" id="spinSpeed1Value">0.15</span>
                </div>
            </div>
            <div class="electron-controls">
                <h3 style="margin: 0 0 10px 0; color: #66ff66;">Electron 2</h3>
                <div class="slider-container">
                    <label for="orbitalRadius2">Orbital Radius</label>
                    <input type="range" id="orbitalRadius2" min="50" max="500" value="250" step="10">
                    <span class="value-display" id="orbitalRadius2Value">250</span>
                </div>
                <div class="slider-container">
                    <label for="orbitalSpeed2">Orbital Speed</label>
                    <input type="range" id="orbitalSpeed2" min="0.5" max="5" value="1.5" step="0.1">
                    <span class="value-display" id="orbitalSpeed2Value">1.5</span>
                </div>
                <div class="slider-container">
                    <label for="spinSpeed2">Spin Speed</label>
                    <input type="range" id="spinSpeed2" min="0.01" max="0.5" value="0.15" step="0.01">
                    <span class="value-display" id="spinSpeed2Value">0.15</span>
                </div>
            </div>
            <div class="electron-controls">
                <div class="slider-container">
                    <label for="rippleLength">Ripple Length</label>
                    <input type="range" id="rippleLength" min="100" max="500" value="200" step="10">
                    <span class="value-display" id="rippleLengthValue">200</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('waterCanvas');
        const ctx = canvas.getContext('2d');

        // Get slider elements for both electrons
        function getSliderElements(num) {
            return {
                orbitalRadius: document.getElementById(`orbitalRadius${num}`),
                orbitalSpeed: document.getElementById(`orbitalSpeed${num}`),
                spinSpeed: document.getElementById(`spinSpeed${num}`),
                orbitalRadiusValue: document.getElementById(`orbitalRadius${num}Value`),
                orbitalSpeedValue: document.getElementById(`orbitalSpeed${num}Value`),
                spinSpeedValue: document.getElementById(`spinSpeed${num}Value`)
            };
        }

        const electron1Sliders = getSliderElements(1);
        const electron2Sliders = getSliderElements(2);
        const rippleLengthSlider = document.getElementById('rippleLength');
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

        // Create electron objects
        function createElectron(color, radius) {
            return {
                x: center.x + radius,
                y: center.y,
                rotation: 0,
                orbitalAngle: 0,
                size: 5,
                color: color
            };
        }

        let electron1 = createElectron('#6666ff', 150);
        let electron2 = createElectron('#66ff66', 250);

        // Ripples array
        let ripples = [];

        // Update slider value displays
        function setupSliderListeners(sliders) {
            sliders.orbitalRadius.addEventListener('input', (e) => {
                sliders.orbitalRadiusValue.textContent = e.target.value;
            });
            sliders.orbitalSpeed.addEventListener('input', (e) => {
                sliders.orbitalSpeedValue.textContent = e.target.value;
            });
            sliders.spinSpeed.addEventListener('input', (e) => {
                sliders.spinSpeedValue.textContent = e.target.value;
            });
        }

        setupSliderListeners(electron1Sliders);
        setupSliderListeners(electron2Sliders);
        rippleLengthSlider.addEventListener('input', (e) => {
            rippleLengthValue.textContent = e.target.value;
        });

        // Add ESC key handler
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                isPaused = !isPaused;
            }
        });

        function createRipple(x, y, angle, color) {
            ripples.push({
                x,
                y,
                radius: 1,
                angle: angle,
                opacity: 1,
                maxRadius: parseInt(rippleLengthSlider.value),
                color: color
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

        function drawElectron(x, y, rotation, color) {
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(rotation);
            
            ctx.beginPath();
            ctx.arc(0, 0, 10, 0, Math.PI * 2);
            ctx.fillStyle = color;
            ctx.fill();
            
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

        function updateElectron(electron, sliders) {
            if (isPaused) return;

            const orbitalRadius = parseInt(sliders.orbitalRadius.value);
            const orbitalSpeed = parseFloat(sliders.orbitalSpeed.value);
            const spinSpeed = parseFloat(sliders.spinSpeed.value);
            
            electron.orbitalAngle += orbitalSpeed * 0.02;
            electron.x = center.x + Math.cos(electron.orbitalAngle) * orbitalRadius;
            electron.y = center.y + Math.sin(electron.orbitalAngle) * orbitalRadius;
            electron.rotation += spinSpeed;

            for (let i = 0; i < 4; i++) {
                const angle = electron.rotation + (Math.PI / 2 * i);
                createRipple(
                    electron.x + Math.cos(angle) * 10,
                    electron.y + Math.sin(angle) * 10,
                    angle,
                    electron.color
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
                ctx.strokeStyle = ripple.color.replace('ff', Math.floor(ripple.opacity * 255).toString(16).padStart(2, '0'));
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.beginPath();
                ctx.arc(
                    ripple.x + Math.cos(ripple.angle) * ripple.radius * 0.5,
                    ripple.y + Math.sin(ripple.angle) * ripple.radius * 0.5,
                    ripple.radius * 0.3,
                    ripple.angle,
                    ripple.angle + Math.PI * 1.5
                );
                const spiralColor = ripple.color.replace('ff', Math.floor(ripple.opacity * 0.7 * 255).toString(16).padStart(2, '0'));
                ctx.strokeStyle = spiralColor;
                ctx.stroke();
            });
        }

        function drawOrbitalPaths() {
            const radius1 = parseInt(electron1Sliders.orbitalRadius.value);
            const radius2 = parseInt(electron2Sliders.orbitalRadius.value);
            
            ctx.beginPath();
            ctx.arc(center.x, center.y, radius1, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(100, 100, 255, 0.2)';
            ctx.lineWidth = 1;
            ctx.stroke();

            ctx.beginPath();
            ctx.arc(center.x, center.y, radius2, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(100, 255, 100, 0.2)';
            ctx.lineWidth = 1;
            ctx.stroke();
        }

        function animate() {
            ctx.fillStyle = '#002222';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            drawOrbitalPaths();
            updateElectron(electron1, electron1Sliders);
            updateElectron(electron2, electron2Sliders);
            updateRipples();
            drawRipples();
            drawProton();
            drawElectron(electron1.x, electron1.y, electron1.rotation, electron1.color);
            drawElectron(electron2.x, electron2.y, electron2.rotation, electron2.color);

            requestAnimationFrame(animate);
        }

        animate();
    </script>
</body>
</html>
