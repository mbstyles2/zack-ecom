<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZACK | Institution Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

        :root {
            --jumia-orange: #f68b1e;
            --primary-grad: linear-gradient(135deg, #6e8efb, #a777e3);
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --card-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: #333;
        }

        /* --- HEADER ALIGNED IN ONE ROW --- */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--white);
            padding: 10px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            gap: 15px;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            flex-shrink: 0;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 500px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 20px;
            border-radius: 50px;
            border: 1px solid #ddd;
            outline: none;
        }

        .icons {
            display: flex;
            gap: 20px;
            font-size: 20px;
            cursor: pointer;
        }

        /* --- WELCOME AREA --- */
        .welcome-section {
            text-align: center;
            padding: 40px 5% 20px;
        }

        .welcome-section h1 {
            font-size: 28px;
            color: #2d3436;
        }

        /* --- MAIN ACTION CARDS (From Sketch) --- */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px 5%;
        }

        .action-card {
            background: var(--white);
            padding: 40px 20px;
            border-radius: 20px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .action-card:hover {
            transform: translateY(-10px);
            border-color: var(--jumia-orange);
            box-shadow: 0 15px 35px rgba(246, 139, 30, 0.2);
        }

        .action-card i {
            font-size: 40px;
            color: var(--jumia-orange);
        }

        .action-card h3 {
            margin: 0;
            font-size: 20px;
            color: #444;
        }

        .action-card p {
            font-size: 14px;
            color: #777;
        }

        /* --- PRODUCT DISPLAY AREA --- */
        .content-area {
            padding: 20px 5%;
            display: none; /* Hidden until a card is clicked */
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .item-card {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            position: relative;
        }

        .item-card img {
            width: 100%;
            height: 150px;
            object-fit: contain;
        }

        .quote-btn {
            background: var(--primary-grad);
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            margin-top: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        /* --- MOBILE VIEW --- */
        @media (max-width: 600px) {
            .navbar { padding: 10px; gap: 8px; }
            .logo { font-size: 18px; }
            .icons { gap: 10px; }
            .action-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">ZACK</div>
    <div class="search-bar">
        <input type="text" placeholder="Search orders or products...">
    </div>
    <div class="icons">
        <span title="Account">👤</span>
        <span title="Cart">🛒</span>
    </div>
</div>

<div class="welcome-section">
    <h1>Welcome, <span id="instName">[Institution Name]</span></h1>
    <p>Managing supplies for your school effectively.</p>
</div>

<div class="action-grid">
    <div class="action-card" onclick="showSection('orders')">
        <i>📦</i>
        <h3>Order Supplies (Bulk)</h3>
        <p>Purchase stock for your institution at bulk rates.</p>
    </div>

    <div class="action-card" onclick="showSection('quotation')">
        <i>📄</i>
        <h3>Request Quotation</h3>
        <p>Select products and submit to admin (No Payment).</p>
    </div>

    <div class="action-card" onclick="showSection('view')">
        <i>🔔</i>
        <h3>View Orders</h3>
        <p>Trace all orders and admin notifications.</p>
    </div>
</div>

<div id="displayArea" class="content-area">
    <h2 id="sectionTitle">Products</h2>
    <div id="productGrid" class="product-grid"></div>
    <button id="submitQuote" class="quote-btn" style="margin-top:30px; display:none;" onclick="submitToAdmin()">Submit Quotation to Admin</button>
</div>

<script>
    const products = [
        { id: 1, name: "Mathematical Set (Box of 50)", price: 150, img: "https://via.placeholder.com/150?text=Math+Sets" },
        { id: 2, name: "A4 Exercise Books (Carton)", price: 85, img: "https://via.placeholder.com/150?text=Books" },
        { id: 3, name: "School Chalk (White/Coloured)", price: 20, img: "https://via.placeholder.com/150?text=Chalk" },
        { id: 4, name: "Lab Coats (Pack of 20)", price: 200, img: "https://via.placeholder.com/150?text=Lab+Coats" }
    ];

    let selectedForQuote = [];

    function showSection(type) {
        const area = document.getElementById('displayArea');
        const grid = document.getElementById('productGrid');
        const title = document.getElementById('sectionTitle');
        const btn = document.getElementById('submitQuote');
        
        area.style.display = 'block';
        grid.innerHTML = '';
        btn.style.display = 'none';

        if(type === 'quotation') {
            title.innerText = "Select Items for Quotation";
            btn.style.display = 'block';
            products.forEach(p => {
                grid.innerHTML += `
                    <div class="item-card">
                        <img src="${p.img}">
                        <h4>${p.name}</h4>
                        <p>Unit Bulk Price:KSH ${p.price}</p>
                        <button class="quote-btn" onclick="addToQuote('${p.name}')">Select Item</button>
                    </div>
                `;
            });
        } else if (type === 'orders') {
            title.innerText = "Bulk Supply Shop";
            // Logic for bulk shopping with checkout...
            grid.innerHTML = "<p>Loading shop products...</p>";
        } else {
            title.innerText = "Order Tracking & Notifications";
            grid.innerHTML = "<div style='padding:20px; background:#fff; border-radius:10px; width:100%'>No recent notifications from Admin.</div>";
        }
        
        // Scroll down to the content
        area.scrollIntoView({ behavior: 'smooth' });
    }

    function addToQuote(name) {
        selectedForQuote.push(name);
        alert(name + " added to your list.");
    }

    function submitToAdmin() {
        if(selectedForQuote.length === 0) {
            alert("Please select at least one item first.");
            return;
        }
        alert("Your quotation request for " + selectedForQuote.length + " items has been submitted to Admin. No payment required at this stage.");
        selectedForQuote = [];
    }
</script>

</body>
</html>
