<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZACK | Student Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

        :root {
            --jumia-orange: #f68b1e;
            --primary-grad: linear-gradient(135deg, #6e8efb, #a777e3);
            --dark-text: #2d3436;
            --light-bg: #f4f6f8;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        /* --- MODERN NAV BAR (Fixed & Aligned) --- */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--white);
            padding: 10px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between; /* Spreads items out */
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            gap: 15px;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
            flex-shrink: 0;
        }

        .search-wrapper {
            flex-grow: 1;
            max-width: 600px;
            position: relative;
        }

        .search-wrapper input {
            width: 100%;
            padding: 12px 20px;
            border-radius: 50px;
            border: 2px solid #eee;
            outline: none;
            transition: 0.3s;
            font-size: 14px;
            box-sizing: border-box;
        }

        .search-wrapper input:focus {
            border-color: #a777e3;
            box-shadow: 0 0 10px rgba(167, 119, 227, 0.2);
        }

        .icon-group {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }

        .nav-icon {
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            position: relative;
            font-size: 22px;
        }

        .nav-icon:hover { color: var(--jumia-orange); transform: translateY(-2px); }

        .badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: var(--jumia-orange);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 50px;
            font-weight: bold;
        }

        /* --- SELECTION CARD (Your Sketch) --- */
        .filter-container {
            padding: 40px 5%;
            display: flex;
            justify-content: center;
        }

        .glass-card {
            background: var(--white);
            width: 100%;
            max-width: 450px;
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .glass-card h2 { margin-top: 0; text-align: center; font-size: 20px; color: #444; }

        .glass-card select {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border-radius: 12px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            font-family: inherit;
            outline: none;
        }

        .btn-continue {
            width: 100%;
            padding: 15px;
            margin-top: 15px;
            border: none;
            border-radius: 12px;
            background: var(--primary-grad);
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(110, 142, 251, 0.3);
            transition: 0.3s;
        }

        .btn-continue:hover { transform: scale(1.02); opacity: 0.9; }

        /* --- PRODUCT CARDS (Modern & Colorful) --- */
        main {
            padding: 20px 5%;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: var(--white);
            border-radius: 20px;
            padding: 15px;
            text-align: center;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            border: 1px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-12px);
            border-color: #a777e3;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 15px;
            border-radius: 12px;
        }

        .product-card h4 { margin: 10px 0; font-size: 16px; color: #333; }

        .price {
            font-size: 22px;
            font-weight: 800;
            color: var(--jumia-orange);
            margin-bottom: 15px;
        }

        .add-btn {
            background: var(--dark-text);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: 0.3s;
        }

        .add-btn:hover { background: var(--jumia-orange); }

        /* --- MOBILE RESPONSIVENESS --- */
        @media (max-width: 600px) {
            .navbar {
                padding: 10px 3%;
                gap: 8px;
            }
            .logo { font-size: 18px; }
            .search-wrapper input { padding: 8px 12px; font-size: 12px; }
            .icon-group { gap: 10px; }
            .nav-icon { font-size: 18px; }
            
            main {
                grid-template-columns: repeat(2, 1fr); /* 2 per row on mobile */
                gap: 12px;
                padding: 10px;
            }
            .product-card { padding: 10px; border-radius: 12px; }
            .product-card img { height: 120px; }
            .price { font-size: 16px; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">ZACK</div>
    
    <div class="search-wrapper">
        <input type="text" placeholder="Search for products...">
    </div>

    <div class="icon-group">
        <div class="nav-icon" title="Account">👤</div>
        <div class="nav-icon" title="Cart">
            🛒<span class="badge">0</span>
        </div>
    </div>
</div>

<section class="filter-container">
    <div class="glass-card">
        <h2>School Requirements</h2>
        <select><option>Select Institution</option></select>
        <select><option>Select Level</option></select>
        <select><option>Select Class</option></select>
        <select>
            <option>Full Kit</option>
            <option>Individual Items</option>
            <option>Top-up Essentials</option>
        </select>
        <button class="btn-continue">CONTINUE</button>
    </div>
</section>

<main id="productDisplay">
    <div class="product-card">
        <img src="https://via.placeholder.com/200x200/f0f0f0/f68b1e?text=School+Blazer" alt="Product">
        <h4>Premium Blazer</h4>
        <div class="price">KSH 450</div>
        <button class="add-btn">Add to Cart</button>
    </div>
    
    <div class="product-card">
        <img src="https://via.placeholder.com/200x200/f0f0f0/f68b1e?text=School+Bag" alt="Product">
        <h4>Classic Backpack</h4>
        <div class="price"> KSH 550</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://via.placeholder.com/200x200/f0f0f0/f68b1e?text=Tie" alt="Product">
        <h4>Silk School Tie</h4>
        <div class="price">KSH 100</div>
        <button class="add-btn">Add to Cart</button>
    </div>

    <div class="product-card">
        <img src="https://via.placeholder.com/200x200/f0f0f0/f68b1e?text=Shoes" alt="Product">
        <h4>Black Formal Shoes</h4>
        <div class="price">KSH 3000</div>
        <button class="add-btn">Add to Cart</button>
    </div>
</main>

<script>
    // You can plug your existing filtering logic here
    console.log("ZACK Dashboard Loaded");
</script>

</body>
</html>
