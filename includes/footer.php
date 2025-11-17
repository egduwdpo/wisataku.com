<style>
    .footer {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        color: white;
        padding: 60px 0 0;
        margin-top: 80px;
        position: relative;
        overflow: hidden;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
    }

    .footer-wave {
        position: absolute;
        top: -100px;
        left: 0;
        width: 100%;
        height: 100px;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%230f2027" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat;
        background-size: cover;
    }

    .footer-content {
        position: relative;
        z-index: 1;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
        margin-bottom: 50px;
    }

    .footer-section h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .footer-section h3 i {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.2rem;
    }

    .footer-brand {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .footer-brand i {
        font-size: 2.5rem;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .footer-description {
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.8;
        margin-bottom: 20px;
    }

    .footer-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
        color: rgba(255, 255, 255, 0.9);
        transition: all 0.3s;
    }

    .footer-contact-item:hover {
        transform: translateX(5px);
        color: #38ef7d;
    }

    .footer-contact-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }

    .footer-contact-text {
        flex: 1;
    }

    .footer-contact-label {
        font-weight: 700;
        font-size: 0.9rem;
        color: #38ef7d;
        margin-bottom: 5px;
    }

    .footer-contact-value {
        font-size: 1rem;
        line-height: 1.6;
    }

    .footer-quick-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-quick-links li {
        margin-bottom: 12px;
    }

    .footer-quick-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        padding: 8px 0;
    }

    .footer-quick-links a:hover {
        color: #38ef7d;
        transform: translateX(5px);
    }

    .footer-quick-links a i {
        font-size: 0.8rem;
        transition: transform 0.3s;
    }

    .footer-quick-links a:hover i {
        transform: translateX(5px);
    }

    .footer-social {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .footer-social-btn {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        font-size: 1.2rem;
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .footer-social-btn:hover {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .footer-bottom {
        background: rgba(0, 0, 0, 0.3);
        padding: 25px 0;
        text-align: center;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-bottom p {
        margin: 0;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
    }

    .footer-bottom a {
        color: #38ef7d;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }

    .footer-bottom a:hover {
        color: #11998e;
    }

    @media (max-width: 768px) {
        .footer {
            padding: 40px 0 0;
        }

        .footer-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .footer-brand {
            font-size: 1.8rem;
        }
    }
</style>

<footer class="footer">
    <div class="footer-wave"></div>
    
    <div class="footer-content">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- About Section -->
                <div class="footer-section">
                    <div class="footer-brand">
                        <i class="bi bi-geo-alt-fill"></i>
                        Wisata Sulsel
                    </div>
                    <p class="footer-description">
                        Platform terpercaya untuk menjelajahi keindahan destinasi wisata Sulawesi Selatan. 
                        Temukan pengalaman tak terlupakan di setiap sudut pulau.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="footer-social-btn" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="footer-social-btn" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="footer-social-btn" title="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="footer-social-btn" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Contact Section -->
                <div class="footer-section">
                    <h3>
                        <i class="bi bi-chat-dots-fill"></i>
                        Hubungi Kami
                    </h3>
                    
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="footer-contact-text">
                            <div class="footer-contact-label">Alamat</div>
                            <div class="footer-contact-value">
                                Jl. Jend. Ahmad Yani Km. 6,<br>
                                Parepare Sulawesi Selatan
                            </div>
                        </div>
                    </div>

                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div class="footer-contact-text">
                            <div class="footer-contact-label">Telepon</div>
                            <div class="footer-contact-value">+6281242210604</div>
                        </div>
                    </div>

                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="bi bi-globe"></i>
                        </div>
                        <div class="footer-contact-text">
                            <div class="footer-contact-label">Website</div>
                            <div class="footer-contact-value">wisatasulsel.com</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links Section -->
                <div class="footer-section">
                    <h3>
                        <i class="bi bi-list-ul"></i>
                        Menu Cepat
                    </h3>
                    <ul class="footer-quick-links">
                        <li>
                            <a href="../user/destinations.php">
                                <i class="bi bi-chevron-right"></i>
                                Destinasi Wisata
                            </a>
                        </li>
                        <li>
                            <a href="../user/gallery.php">
                                <i class="bi bi-chevron-right"></i>
                                Galeri Foto
                            </a>
                        </li>
                        <li>
                            <a href="../user/chat.php">
                                <i class="bi bi-chevron-right"></i>
                                Hubungi Admin
                            </a>
                        </li>
                        <li>
                            <a href="../user/dashboard.php">
                                <i class="bi bi-chevron-right"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="../auth/login.php">
                                <i class="bi bi-chevron-right"></i>
                                Login / Register
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-container">
            <p>
                &copy; <?= date('Y') ?> <a href="/">Wisata Sulsel</a>. All Rights Reserved. 
                Made with: IWN | tech. JASA BUAT WEBSITE TERPERCAYA
            </p>
        </div>
    </div>
</footer>