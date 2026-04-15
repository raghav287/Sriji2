<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <title>Contact - Sriji Vastra Shingar Sewa</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/mobile_menu.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/scroll_button.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/venobox.min.css">
    <link rel="stylesheet" href="assets/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/jquery.pwstabs.css">
    <link rel="stylesheet" href="assets/css/range_slider.css">
    <link rel="stylesheet" href="assets/css/multiple-image-video.css">
    <link rel="stylesheet" href="assets/css/animated_barfiller.css">
    <link rel="stylesheet" href="assets/css/custom_spacing.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/responsive.css">

    <style>
    .page_banner {
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        /* aspect-ratio: 38 / 5; */
        min-height: 160px;
    }

    @media (max-width: 768px) {
        .page_banner {
            aspect-ratio: unset;
            min-height: 200px;
        }
    }

    /* Ensure banner background keeps its aspect ratio across breakpoints */
    .page_banner {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    @media (max-width: 768px) {
        .page_banner {
            background-size: cover;
            /* keep ratio while filling smaller screens */
        }
    }
    </style>
</head>

<body class="default_home">

    <!--=========================
        HEADER START
    ==========================-->
    <?php include("includes/header.php") ?>
    <!--=========================
        HEADER END
    ==========================-->


    <!--=========================
        PAGE BANNER START
    ==========================-->
    <section class="page_banner" style="background: url(assets/images/background/ban2.png);">
        <div class="page_banner_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="page_banner_text wow fadeInUp">
                            <h1>FAQ</h1>
                            <ul>
                                <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="#">FAQ</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        PAGE BANNER START
    ==========================-->


    <!--============================
        FAQ SECTION START
    =============================-->
    <section class="faq_section mt_75 mb_100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="section_heading_2 section_heading mb_30">
                        <h3>Frequently Asked <span>Questions</span></h3>
                        <p>Find answers to common questions about our products, orders, and policies.</p>
                    </div>

                    <div class="accordion accordion-flush" id="faqAccordion">
                        <!-- Question 1 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false"
                                    aria-controls="collapseOne">
                                    What is Sriji Vastra Shingar Sewa?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Sriji Vastra Shingar Sewa is your one-stop destination for handcrafted spiritual
                                    dresses (Poshak), jewelry, and accessories for various Devi-Devtas (idols). We
                                    specialize in providing high-quality, beautifully designed items to enhance your
                                    worship space.
                                </div>
                            </div>
                        </div>

                        <!-- Question 2 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false"
                                    aria-controls="collapseTwo">
                                    How can I place a customized order?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    You can easily place a custom order by contacting us on WhatsApp at +91 9115936593.
                                    Our team will guide you through the process, take your measurements, and help you
                                    choose the design and color for your idol's dress.
                                </div>
                            </div>
                        </div>

                        <!-- Question 3 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false"
                                    aria-controls="collapseThree">
                                    What is the delivery time for my order?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Standard orders are usually dispatched within 24–48 hours. Delivery takes 2–5
                                    business days for metro cities and 4–8 business days for other locations. Custom
                                    orders take 3–10 working days depending on the design's complexity.
                                </div>
                            </div>
                        </div>

                        <!-- Question 4 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false"
                                    aria-controls="collapseFour">
                                    Do you offer free shipping?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Yes! We offer <strong>FREE SHIPPING</strong> on all prepaid orders above ₹500. For
                                    orders below ₹500, a nominal shipping charge of ₹50 applies. Cash on Delivery (COD)
                                    orders have separate charges based on the order value.
                                </div>
                            </div>
                        </div>

                        <!-- Question 5 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false"
                                    aria-controls="collapseFive">
                                    Can I return or exchange a product?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Yes, you can raise a return or exchange request within 2 days of delivery for
                                    genuine issues. However, an <strong>unboxing video is mandatory</strong> for all
                                    such requests to ensure a fair process for both sides.
                                </div>
                            </div>
                        </div>

                        <!-- Question 6 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false"
                                    aria-controls="collapseSix">
                                    Are there any items that cannot be returned?
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Yes, customized products, jewelry, Laddu Gopal furniture, and items priced below
                                    ₹150 are not eligible for return or exchange due to their unique or delicate nature.
                                </div>
                            </div>
                        </div>

                        <!-- Question 7 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingSeven">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false"
                                    aria-controls="collapseSeven">
                                    What should I do if I receive a damaged product?
                                </button>
                            </h2>
                            <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    If your package appears tampered with or damaged, please do not accept it. If you
                                    discover damage after opening, contact us immediately on WhatsApp (+91 9115936593)
                                    with your mandatory unboxing video within 24 hours.
                                </div>
                            </div>
                        </div>

                        <!-- Question 8 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingEight">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false"
                                    aria-controls="collapseEight">
                                    Can I cancel my order?
                                </button>
                            </h2>
                            <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Orders can only be cancelled before they are dispatched. Once an order has left our
                                    warehouse, cancellation is not possible. For custom orders, once confirmed and paid,
                                    they cannot be cancelled.
                                </div>
                            </div>
                        </div>

                        <!-- Question 9 -->
                        <div class="accordion-item border mb-3 rounded shadow-sm">
                            <h2 class="accordion-header" id="headingNine">
                                <button class="accordion-button collapsed fw-bold py-3" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false"
                                    aria-controls="collapseNine">
                                    How do I track my order?
                                </button>
                            </h2>
                            <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Once your order is dispatched, we will provide you with a tracking number and link
                                    via SMS or WhatsApp so you can monitor its progress in real-time.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="contact_info mt_50 p-4 border rounded bg-light text-center">
                        <h4 class="mb-3">Still have questions?</h4>
                        <p class="mb-3">Our friendly team is here to help you.</p>
                        <a href="https://wa.me/919115936593" target="_blank" class="common_btn py-2 px-4">Chat with us on
                            WhatsApp</a>
                    </div> -->
                </div>
            </div>
        </div>
    </section>
    <!--============================
        FAQ SECTION END
    =============================-->


    <!--=========================
        FOOTER 2 START
    ==========================-->
    <?php include("includes/footer.php") ?>
    <!--=========================
        FOOTER 2 END
    ==========================-->
    <a href="https://wa.me/919115936593" target="_blank"
        class="position-fixed d-flex align-items-center justify-content-center" style="
      bottom:25px;
      right:25px;
      padding:10px;
      width:65px;
      height:65px;
      background-color:#25D366;
      border-radius:50%;
      box-shadow:0 8px 20px rgba(0,0,0,0.2);
      z-index:999;
      transition:all 0.3s ease;
   " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="30" alt="WhatsApp">
    </a>

    <!--==========================
        SCROLL BUTTON START
    ===========================-->
    <!-- <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div> -->
    <!--==========================
        SCROLL BUTTON END
    ===========================-->


    <!--jquery library js-->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <!--bootstrap js-->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!--font-awesome js-->
    <script src="assets/js/Font-Awesome.js"></script>
    <!--counter js-->
    <script src="assets/js/jquery.waypoints.min.js"></script>
    <script src="assets/js/jquery.countup.min.js"></script>
    <!--nice select js-->
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <!--select 2 js-->
    <script src="assets/js/select2.min.js"></script>
    <!--simply countdown js-->
    <script src="assets/js/simplyCountdown.js"></script>
    <!--slick slider js-->
    <script src="assets/js/slick.min.js"></script>
    <!--venobox js-->
    <script src="assets/js/venobox.min.js"></script>
    <!--wow js-->
    <script src="assets/js/wow.min.js"></script>
    <!--marquee js-->
    <script src="assets/js/jquery.marquee.min.js"></script>
    <!--pws tabs js-->
    <script src="assets/js/jquery.pwstabs.min.js"></script>
    <!--scroll button js-->
    <script src="assets/js/scroll_button.js"></script>
    <!--youtube background js-->
    <script src="assets/js/jquery.youtube-background.min.js"></script>
    <!--range slider js-->
    <script src="assets/js/range_slider.js"></script>
    <!--sticky sidebar js-->
    <script src="assets/js/sticky_sidebar.js"></script>
    <!--multiple image upload js-->
    <script src="assets/js/multiple-image-video.js"></script>
    <!--animated barfiller js-->
    <script src="assets/js/animated_barfiller.js"></script>
    <!--main/custom js-->
    <script src="assets/js/custom.js"></script>

</body>

</html>