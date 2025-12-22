<?php 
session_start();
$pageTitle = "Landing Page";
$pagename = 'landing';
include '../includes/header.php';
?>

<section class="slider">
            <div class="container">
                <div class="slider-content">
                    <h2>JobPro</h2>
                    <h3 id="slider-title">First slide label</h3>
                    <p id="slider-description">get perfect jobs here.</p>
                    <div class="slider-dots">
                        <span class="active" onclick="changeSlide(0)"></span>
                        <span onclick="changeSlide(1)"></span>
                        <span onclick="changeSlide(2)"></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="reviews">
            <div class="container">
                <h2>User reviews:</h2>
                <div class="review-grid">
                    <div class="review">
                        <div class="review-avatar">
                            <!-- picsum image -->
                            <img src="/jobpro/assets/images/profile1.jpg" alt="Avatar">
                        </div>
                        <div class="review-content">
                            <h3>Alex</h3>
                            <div class="stars">★★★★☆</div>
                            <p>This jobpro made it so easy to find jobs that matched my skills. Within a week, I landed several interviews and got hired at my dream company! Highly recommend it to job seekers.</p>
                        </div>
                    </div>
                    <div class="review">
                        <div class="review-avatar">
                            <img src="/jobpro/assets/images/profile2.jpg" alt="Avatar">  
                        </div>
                        <div class="review-content">
                            <h3>Bien</h3>
                            <div class="stars">★★★★★</div>
                            <p>The jobpro helped me find great job opportunities, and the application process was smooth. However, notifications could be more timely. Overall, a solid platform for job hunting.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <script>
const slides = [
    { title: "First slide label", description: "get perfect jobs here." },
    { title: "Second slide label", description: "post your job and find perfect candidate." },
    { title: "Third slide label", description: "we have more than 1000 success storeis." }
];

let currentSlide = 0;

function changeSlide(index) {
    currentSlide = index;
    updateSlider();
}

function updateSlider() {
    const title = document.getElementById('slider-title');
    const description = document.getElementById('slider-description');
    const dots = document.querySelectorAll('.slider-dots span');

    title.textContent = slides[currentSlide].title;
    description.textContent = slides[currentSlide].description;

    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

// Auto-rotate slides
setInterval(() => {
    currentSlide = (currentSlide + 1) % slides.length;
    updateSlider();
}, 2000);
        </script>

<?php include '../includes/footer.php'; ?>