<?php 
session_start();
$pageTitle = "About Us";
$pagename = 'about';
include '../includes/header.php'; 
?>

<div class="container about-us-page">
    <h1>About Us</h1>
    
    <section class="mission-section">
        <h2>Our Mission</h2>
        <p>
            At JobPro, our mission is to connect talented individuals with outstanding job opportunities in various industries. We strive to create a platform that not only helps job seekers find their dream jobs but also assists employers in discovering the right talent to grow their businesses. 
        </p>
    </section>

    <section class="vision-section">
        <h2>Our Vision</h2>
        <p>
            Our vision is to be the leading job portal in Sri Lanka and beyond, providing innovative solutions that empower both job seekers and employers. We envision a world where every individual can find fulfilling work that matches their skills and aspirations.
        </p>
    </section>

    <section class="values-section">
        <h2>Our Values</h2>
        <ul>
            <li><strong>Integrity:</strong> We believe in being honest and transparent in all our dealings.</li>
            <li><strong>Innovation:</strong> We are committed to continuous improvement and embracing new technologies.</li>
            <li><strong>Collaboration:</strong> We value teamwork and partnership, working together to achieve common goals.</li>
            <li><strong>Empowerment:</strong> We empower job seekers and employers with the tools and resources they need to succeed.</li>
        </ul>
    </section>

    <section class="team-section">
        <h2>Meet Our Team</h2>
        <p>
            Our diverse team is made up of experienced professionals passionate about making a difference in the job market. Together, we bring a wealth of knowledge and expertise to ensure JobPro is a reliable resource for both job seekers and employers.
        </p>
        <div class="team-members">
            <div class="team-member">
                <h3>John Doe</h3>
                <p>CEO & Founder</p>
                <p>With over 15 years of experience in the HR industry, John leads JobPro with a vision to transform the recruitment landscape.</p>
            </div>
            <div class="team-member">
                <h3>Jane Smith</h3>
                <p>Head of Marketing</p>
                <p>Jane is an expert in digital marketing strategies and is responsible for promoting JobPro's mission to a wider audience.</p>
            </div>
            <div class="team-member">
                <h3>Robert Brown</h3>
                <p>Technical Director</p>
                <p>Robert ensures our platform remains cutting-edge and user-friendly, leveraging the latest technologies to enhance the user experience.</p>
            </div>
        </div>
    </section>

    <section class="history-section">
        <h2>Our History</h2>
        <p>
            Founded in 2020, JobPro was created to address the challenges faced by job seekers and employers in the hiring process. Since our inception, we have grown into a trusted platform with thousands of registered users and a wide range of job listings across various industries. We are committed to expanding our services and continually improving the experience for our users.
        </p>
    </section>

    <section class="contact-section">
        <h2>Get in Touch</h2>
        <p>If you have any questions, suggestions, or feedback, feel free to <a href="contact.php">contact us</a>.</p>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
