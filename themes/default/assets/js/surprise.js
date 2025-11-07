/**
 * SÜRPRİZ KONFETİ & HAVAİ FİŞEK ANIMASYONU
 * Son 1 saat içinde gösterilmediyse tekrar gösterir
 */

(function() {
    'use strict';

    // Son gösterim zamanını kontrol et
    const lastShownTime = localStorage.getItem('surpriseLastShown');
    const currentTime = new Date().getTime();
    const oneHourInMs = 60 * 60 * 1000; // 1 saat = 60 dakika * 60 saniye * 1000 ms

    // Eğer son 1 saat içinde gösterildiyse çık
    if (lastShownTime) {
        const timeDifference = currentTime - parseInt(lastShownTime);
        
        if (timeDifference < oneHourInMs) {
            // Henüz 1 saat geçmemiş
            console.log('Sürpriz animasyonu ' + Math.round((oneHourInMs - timeDifference) / 1000 / 60) + ' dakika sonra tekrar gösterilecek');
            return;
        }
    }

    // Sayfa yüklendiğinde başlat
    window.addEventListener('load', function() {
        setTimeout(showSurprise, 500);
    });

    function showSurprise() {
        // Overlay oluştur
        const overlay = document.createElement('div');
        overlay.id = 'surpriseOverlay';
        overlay.innerHTML = `
            <div class="surprise-container">
                <div class="surprise-content">
                    <div class="surprise-emoji">🎉</div>
                    <h1 class="surprise-title">SÜRPRİZ!</h1>
                    <p class="surprise-text">Yepyeni Modern Tasarımımız!</p>
                    <div class="surprise-features">
                        <span class="feature-badge">✨ Modern Arayüz</span>
                        <span class="feature-badge">🌙 Dark Mode</span>
                        <span class="feature-badge">🎨 Renkli Temalar</span>
                        <span class="feature-badge">⚡ Hızlı & Kolay</span>
                    </div>
                    <button class="surprise-btn" onclick="closeSurprise()">
                        Keşfet! 🚀
                    </button>
                </div>
            </div>
            <canvas id="confettiCanvas"></canvas>
        `;

        document.body.appendChild(overlay);

        // CSS stilleri ekle
        addSurpriseStyles();

        // Animasyonları başlat
        setTimeout(() => {
            overlay.classList.add('active');
            startConfetti();
            startFireworks();
        }, 100);

        // Otomatik kapanma (15 saniye)
        setTimeout(() => {
            closeSurprise();
        }, 15000);
    }

    function addSurpriseStyles() {
    const style = document.createElement('style');
    style.textContent = `
        #surpriseOverlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            z-index: 999999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            opacity: 0;
            transition: opacity 0.5s ease;
            margin: 0 !important;
            padding: 0 !important;
        }

        #surpriseOverlay.active {
            opacity: 1;
        }

        #confettiCanvas {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            pointer-events: none;
            z-index: 1;
        }

        .surprise-container {
            position: relative !important;
            z-index: 2 !important;
            text-align: center;
            transform: scale(0.5);
            opacity: 0;
            animation: surpriseBounce 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.3s forwards;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes surpriseBounce {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .surprise-content {
            background: white;
            padding: 3rem 4rem;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            margin: auto;
        }

        .surprise-emoji {
            font-size: 5rem;
            animation: rotate 2s ease-in-out infinite;
            margin-bottom: 1rem;
            line-height: 1;
        }

        @keyframes rotate {
            0%, 100% { transform: rotate(-10deg) scale(1); }
            50% { transform: rotate(10deg) scale(1.2); }
        }

        .surprise-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0.5rem 0;
            animation: pulse 1.5s ease-in-out infinite;
            line-height: 1.2;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .surprise-text {
            font-size: 1.5rem;
            color: #495057;
            margin: 1rem 0 2rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .surprise-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin: 2rem 0;
        }

        .feature-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            animation: fadeInUp 0.6s ease-out backwards;
            white-space: nowrap;
        }

        .feature-badge:nth-child(1) { animation-delay: 0.8s; }
        .feature-badge:nth-child(2) { animation-delay: 1s; }
        .feature-badge:nth-child(3) { animation-delay: 1.2s; }
        .feature-badge:nth-child(4) { animation-delay: 1.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .surprise-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .surprise-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        }

        /* Küçük ekranlar için */
        @media (max-width: 768px) {
            .surprise-content {
                padding: 2rem 1.5rem;
                margin: 1rem;
                max-width: 95vw;
            }

            .surprise-title {
                font-size: 2.5rem;
            }

            .surprise-text {
                font-size: 1.2rem;
            }

            .surprise-emoji {
                font-size: 4rem;
            }

            .feature-badge {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            .surprise-btn {
                padding: 0.8rem 2rem;
                font-size: 1rem;
            }
        }

        /* Çok küçük ekranlar */
        @media (max-width: 480px) {
            .surprise-content {
                padding: 1.5rem 1rem;
            }

            .surprise-title {
                font-size: 2rem;
            }

            .surprise-text {
                font-size: 1rem;
            }

            .surprise-emoji {
                font-size: 3rem;
            }
        }

        /* Landscape mode için */
        @media (max-height: 600px) and (orientation: landscape) {
            .surprise-content {
                padding: 1.5rem 2rem;
                max-height: 80vh;
                overflow-y: auto;
            }

            .surprise-emoji {
                font-size: 3rem;
            }

            .surprise-title {
                font-size: 2rem;
            }

            .surprise-text {
                font-size: 1rem;
                margin: 0.5rem 0 1rem;
            }

            .surprise-features {
                margin: 1rem 0;
            }
        }
    `;
    document.head.appendChild(style);
}

    // Konfeti animasyonu
    function startConfetti() {
        const canvas = document.getElementById('confettiCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const confetti = [];
        const confettiCount = 150;
        const gravity = 0.5;
        const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b'];

        class Confetto {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height - canvas.height;
                this.r = Math.random() * 6 + 4;
                this.d = Math.random() * confettiCount;
                this.color = colors[Math.floor(Math.random() * colors.length)];
                this.tilt = Math.floor(Math.random() * 10) - 10;
                this.tiltAngleIncremental = Math.random() * 0.07 + 0.05;
                this.tiltAngle = 0;
            }

            draw() {
                ctx.beginPath();
                ctx.lineWidth = this.r / 2;
                ctx.strokeStyle = this.color;
                ctx.moveTo(this.x + this.tilt + this.r / 4, this.y);
                ctx.lineTo(this.x + this.tilt, this.y + this.tilt + this.r / 4);
                ctx.stroke();
            }

            update() {
                this.tiltAngle += this.tiltAngleIncremental;
                this.y += (Math.cos(this.d) + 3 + this.r / 2) / 2;
                this.x += Math.sin(this.d);
                this.tilt = Math.sin(this.tiltAngle - this.d / 3) * 15;

                if (this.y > canvas.height) {
                    this.x = Math.random() * canvas.width;
                    this.y = -20;
                }
            }
        }

        for (let i = 0; i < confettiCount; i++) {
            confetti.push(new Confetto());
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            confetti.forEach((c) => {
                c.update();
                c.draw();
            });

            if (document.getElementById('surpriseOverlay')) {
                requestAnimationFrame(animate);
            }
        }

        animate();
    }

    // Havai fişek efekti
    function startFireworks() {
        const canvas = document.getElementById('confettiCanvas');
        const ctx = canvas.getContext('2d');

        class Firework {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = canvas.height;
                this.targetY = Math.random() * canvas.height / 2;
                this.speed = 5;
                this.particles = [];
                this.exploded = false;
                this.hue = Math.random() * 360;
            }

            update() {
                if (!this.exploded) {
                    this.y -= this.speed;
                    if (this.y <= this.targetY) {
                        this.explode();
                    }
                } else {
                    this.particles.forEach((p, index) => {
                        p.update();
                        if (p.alpha <= 0) {
                            this.particles.splice(index, 1);
                        }
                    });
                }
            }

            draw() {
                if (!this.exploded) {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
                    ctx.fillStyle = `hsl(${this.hue}, 100%, 50%)`;
                    ctx.fill();
                } else {
                    this.particles.forEach(p => p.draw());
                }
            }

            explode() {
                this.exploded = true;
                for (let i = 0; i < 50; i++) {
                    this.particles.push(new Particle(this.x, this.y, this.hue));
                }
            }
        }

        class Particle {
            constructor(x, y, hue) {
                this.x = x;
                this.y = y;
                this.hue = hue;
                this.angle = Math.random() * Math.PI * 2;
                this.speed = Math.random() * 5 + 2;
                this.vx = Math.cos(this.angle) * this.speed;
                this.vy = Math.sin(this.angle) * this.speed;
                this.alpha = 1;
                this.decay = Math.random() * 0.03 + 0.01;
            }

            update() {
                this.vx *= 0.98;
                this.vy *= 0.98;
                this.vy += 0.2;
                this.x += this.vx;
                this.y += this.vy;
                this.alpha -= this.decay;
            }

            draw() {
                ctx.save();
                ctx.globalAlpha = this.alpha;
                ctx.beginPath();
                ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
                ctx.fillStyle = `hsl(${this.hue}, 100%, 50%)`;
                ctx.fill();
                ctx.restore();
            }
        }

        const fireworks = [];
        let fireworkInterval = setInterval(() => {
            if (document.getElementById('surpriseOverlay')) {
                fireworks.push(new Firework());
            } else {
                clearInterval(fireworkInterval);
            }
        }, 800);

        function animateFireworks() {
            fireworks.forEach((fw, index) => {
                fw.update();
                fw.draw();
                if (fw.exploded && fw.particles.length === 0) {
                    fireworks.splice(index, 1);
                }
            });

            if (document.getElementById('surpriseOverlay')) {
                requestAnimationFrame(animateFireworks);
            }
        }

        animateFireworks();
    }

   // Kapatma fonksiyonu (global)
window.closeSurprise = function() {
    const overlay = document.getElementById('surpriseOverlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.remove();
            document.body.style.overflow = ''; // Scroll'u geri aç
        }, 500);
    }

    // Şu anki zamanı kaydet
    const currentTime = new Date().getTime();
    localStorage.setItem('surpriseLastShown', currentTime);
    
    console.log('Sürpriz animasyonu 1 saat sonra tekrar gösterilecek');
};

})();