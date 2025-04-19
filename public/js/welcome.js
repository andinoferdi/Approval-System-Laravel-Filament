// Tailwind Config
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: {
                    50: "#f0f9ff",
                    100: "#e0f2fe",
                    200: "#bae6fd",
                    300: "#7dd3fc",
                    400: "#38bdf8",
                    500: "#0ea5e9",
                    600: "#0284c7",
                    700: "#0369a1",
                    800: "#075985",
                    900: "#0c4a6e",
                },
                secondary: {
                    50: "#fdf4ff",
                    100: "#fae8ff",
                    200: "#f5d0fe",
                    300: "#f0abfc",
                    400: "#e879f9",
                    500: "#d946ef",
                    600: "#c026d3",
                    700: "#a21caf",
                    800: "#86198f",
                    900: "#701a75",
                },
                accent: {
                    50: "#f8fafc",
                    100: "#f1f5f9",
                    200: "#e2e8f0",
                    300: "#cbd5e1",
                    400: "#94a3b8",
                    500: "#64748b",
                    600: "#475569",
                    700: "#334155",
                    800: "#1e293b",
                    900: "#0f172a",
                },
            },
            fontFamily: {
                sans: ["Plus Jakarta Sans", "sans-serif"],
            },
            animation: {
                float: "float 6s ease-in-out infinite",
                "pulse-slow": "pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite",
            },
            keyframes: {
                float: {
                    "0%, 100%": {
                        transform: "translateY(0)",
                    },
                    "50%": {
                        transform: "translateY(-20px)",
                    },
                },
            },
        },
    },
};

// Initialize Three.js particle scene
function initThreeScene() {
    const container = document.getElementById("scene-container");
    if (!container) return;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(
        75,
        window.innerWidth / window.innerHeight,
        0.1,
        1000
    );
    const renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: true,
    });

    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Create particles
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 1500;

    const posArray = new Float32Array(particlesCount * 3);
    const colorArray = new Float32Array(particlesCount * 3);

    // Create random positions and colors
    for (let i = 0; i < particlesCount * 3; i += 3) {
        // Position
        const radius = 5 + Math.random() * 10;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.random() * Math.PI;

        posArray[i] = radius * Math.sin(phi) * Math.cos(theta);
        posArray[i + 1] = radius * Math.sin(phi) * Math.sin(theta);
        posArray[i + 2] = radius * Math.cos(phi);

        // Neon colors (RGB)
        const colorChoice = Math.random();
        if (colorChoice < 0.33) {
            // Neon blue
            colorArray[i] = 0.1;
            colorArray[i + 1] = 0.7;
            colorArray[i + 2] = 1.0;
        } else if (colorChoice < 0.66) {
            // Neon pink
            colorArray[i] = 1.0;
            colorArray[i + 1] = 0.1;
            colorArray[i + 2] = 0.7;
        } else {
            // Neon purple
            colorArray[i] = 0.7;
            colorArray[i + 1] = 0.1;
            colorArray[i + 2] = 1.0;
        }
    }

    particlesGeometry.setAttribute(
        "position",
        new THREE.BufferAttribute(posArray, 3)
    );
    particlesGeometry.setAttribute(
        "color",
        new THREE.BufferAttribute(colorArray, 3)
    );

    // Material with custom shaders for neon glow effect
    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.05,
        transparent: true,
        opacity: 0.7,
        vertexColors: true,
        blending: THREE.AdditiveBlending,
    });

    const particlesMesh = new THREE.Points(
        particlesGeometry,
        particlesMaterial
    );
    scene.add(particlesMesh);

    camera.position.z = 5;

    // Animation
    function animate() {
        requestAnimationFrame(animate);
        particlesMesh.rotation.x += 0.0005;
        particlesMesh.rotation.y += 0.0007;
        renderer.render(scene, camera);
    }

    animate();

    // Handle window resize
    window.addEventListener("resize", () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
}

// Update progress bar on scroll
function initScrollProgress() {
    // Add progress bar to the DOM if it doesn't exist
    if (!document.querySelector(".progress-bar")) {
        const progressBar = document.createElement("div");
        progressBar.classList.add("progress-bar");
        document.body.prepend(progressBar);
    }

    // Update progress bar width based on scroll position
    window.addEventListener("scroll", () => {
        const winScroll =
            document.body.scrollTop || document.documentElement.scrollTop;
        const height =
            document.documentElement.scrollHeight -
            document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        document.querySelector(".progress-bar").style.width = scrolled + "%";
    });
}

// Initialize all functionality
document.addEventListener("DOMContentLoaded", function () {
    // Initialize Lottie animation
    const heroAnimationEl = document.getElementById("hero-animation");
    if (heroAnimationEl) {
        lottie.loadAnimation({
            container: heroAnimationEl,
            renderer: "svg",
            loop: true,
            autoplay: true,
            path: "https://assets5.lottiefiles.com/packages/lf20_jcikwtux.json", // Workflow animation
        });
    }

    // Initialize particle effect
    initThreeScene();

    // Initialize scroll progress bar
    initScrollProgress();

    // Add active class to flow cards with delay for cascade effect
    const flowCards = document.querySelectorAll(".flow-card");
    flowCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add("active");
        }, index * 300);
    });
});
