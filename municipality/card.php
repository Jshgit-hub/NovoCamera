<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attractions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .attraction-card {
            border-radius: 10px;
            overflow: hidden;
            background-color: #f7f8fa;
            transition: transform 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .attraction-card:hover {
            transform: scale(1.05);
            text-decoration: none;
        }

        .attraction-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .attraction-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 15px;
            color: #000;
        }

        .attraction-location {
            color: #999;
            text-transform: uppercase;
            font-size: 0.875rem;
        }

        .attraction-description {
            margin-top: 10px;
            font-size: 0.95rem;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row text-center mb-4">
            <h1 class="display-4">Must-see attractions</h1>
        </div>

        <!-- Latest Stories Section -->
        <section class="latest-stories py-5" style="background-color: #f9f9f9;">
            <div class="container">
                <div class="row">
                    <!-- Main Story -->
                    <div class="col-lg-7">
                        <div class="main-story position-relative">
                            <img src="../assets/images/bg_1.jpg" alt="Main story image"
                                class="img-fluid rounded w-100">
                            <div
                                class="story-content p-4 position-absolute bottom-0 start-0 bg-dark bg-opacity-50 text-white w-100">
                                <span class="story-category text-uppercase fw-bold" style="font-size: 12px; color: #FF5733;">Activities</span>
                                <h2 class="story-title mt-2 fw-bold" style="font-size: 24px;">8 of the best places for wellness in Costa Rica</h2>
                                <p class="story-meta" style="font-size: 14px; color: #ddd;">Aug 29, 2024 • 6 min read</p>
                                <p style="color: white;">Costa Rica is one of the world's wellness hot spots. Here are our tips for the best retreats to visit.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Secondary Stories -->
                    <div class="col-lg-5">
                        <div class="secondary-stories">
                            <div class="story-item d-flex mb-4">
                                <img src="../assets/images/bg_1.jpg" alt="Secondary story image"
                                    class="img-fluid rounded me-3" style="width: 90px; height: 90px; object-fit: cover;">
                                <div class="story-info flex-grow-1">
                                    <span class="story-category text-uppercase fw-bold" style="font-size: 12px; color: #FF5733;">Destination Practicalities</span>
                                    <h5 class="story-title mt-1 fw-bold text-dark" style="font-size: 18px;">12 things to know before traveling to Seville</h5>
                                    <p class="story-meta" style="font-size: 14px; color: #999;">Aug 29, 2024 • 5 min read</p>
                                </div>
                            </div>
                            <div class="story-item d-flex mb-4">
                                <img src="../assets/images/bg_1.jpg" alt="Secondary story image"
                                    class="img-fluid rounded me-3" style="width: 90px; height: 90px; object-fit: cover;">
                                <div class="story-info flex-grow-1">
                                    <span class="story-category text-uppercase fw-bold" style="font-size: 12px; color: #FF5733;">Festivals & Events</span>
                                    <h5 class="story-title mt-1 fw-bold text-dark" style="font-size: 18px;">When is the best time to visit India?</h5>
                                    <p class="story-meta" style="font-size: 14px; color: #999;">Aug 29, 2024 • 7 min read</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Attraction Cards Section -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <a href="attraction-details-page1.html" class="text-decoration-none">
                    <div class="card attraction-card h-100 bg-light border-0 shadow-sm"
                        style="border-radius: 10px; transition: transform 0.3s ease;">
                        <img src="../assets/images/bg_1.jpg" alt="Centro de Arte Reina Sofía" class="card-img-top">
                        <div class="card-body">
                            <div class="attraction-title">Centro de Arte Reina Sofía</div>
                            <div class="attraction-location text-uppercase text-secondary">Madrid</div>
                            <div class="attraction-description">
                                Home to Picasso’s Guernica, arguably Spain's most famous artwork, the Centro de Arte Reina Sofía is Madrid’s premier collection of contemporary art.
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-4">
                <a href="attraction-details-page2.html" class="text-decoration-none">
                    <div class="card attraction-card h-100 bg-light border-0 shadow-sm"
                        style="border-radius: 10px; transition: transform 0.3s ease;">
                        <img src="image2.jpg" alt="Museo Thyssen-Bornemisza" class="card-img-top">
                        <div class="card-body">
                            <div class="attraction-title">Museo Thyssen-Bornemisza</div>
                            <div class="attraction-location text-uppercase text-secondary">Madrid</div>
                            <div class="attraction-description">
                                The Thyssen-Bornemisza Museum is one of the three points composing Madrid’s Golden Triangle of Art along the Paseo del Prado (Art Walk), together with the...
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-4">
                <a href="attraction-details-page3.html" class="text-decoration-none">
                    <div class="card attraction-card h-100 bg-light border-0 shadow-sm"
                        style="border-radius: 10px; transition: transform 0.3s ease;">
                        <img src="image3.jpg" alt="Casa Batlló" class="card-img-top">
                        <div class="card-body">
                            <div class="attraction-title">Casa Batlló</div>
                            <div class="attraction-location text-uppercase text-secondary">L'Eixample</div>
                            <div class="attraction-description">
                                One of Europe’s strangest residential buildings, Casa Batlló (built 1904–6) is Gaudí at his fantastical best. From its playful facade and marine-world...
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- More Attractions and Carousel Controls -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="#" class="btn btn-outline-primary">View more attractions</a>
            <div>
                <a href="#" class="carousel-control-prev fs-4 text-dark text-decoration-none">&#10094;</a>
                <a href="#" class="carousel-control-next fs-4 text-dark text-decoration-none ms-3">&#10095;</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
