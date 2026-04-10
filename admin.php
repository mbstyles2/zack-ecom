<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZACK ADMIN | Full Functional Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed: 80px;
            --primary: #1a237e;
            --accent: #f68b1e;
            --bg: #f4f7fe;
            --white: #ffffff;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* --- SIDEBAR --- */
        nav {
            width: var(--sidebar-collapsed);
            background: linear-gradient(180deg, #1a237e 0%, #0d1440 100%);
            color: white;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            z-index: 1001;
            white-space: nowrap;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        nav.expanded {
            width: var(--sidebar-width);
        }

        .nav-header {
            padding: 25px 20px;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: 1px;
            color: var(--accent);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .nav-links {
            flex-grow: 1;
            padding: 20px 0;
            overflow-x: hidden;
        }

        .nav-item {
            padding: 18px 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 25px;
            transition: 0.3s;
            position: relative;
        }

        .nav-item i {
            font-size: 22px;
            min-width: 25px;
            text-align: center;
            font-style: normal;
        }

        .nav-item span {
            opacity: 0;
            transition: opacity 0.2s;
            font-weight: 400;
        }

        nav.expanded .nav-item span {
            opacity: 1;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.05);
        }

        .nav-item.active {
            background: rgba(255,255,255,0.1);
            color: var(--accent);
            border-left: 4px solid var(--accent);
        }

        /* --- MAIN CONTENT AREA --- */
        .main-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .top-bar {
            background: var(--white);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .toggle-btn {
            font-size: 24px;
            cursor: pointer;
            color: var(--primary);
            padding: 5px;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        /* Content Sections */
        .view-panel {
            padding: 30px;
            overflow-y: auto;
            flex-grow: 1;
            display: none;
        }

        .view-panel.active {
            display: block;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- UI COMPONENTS --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            margin-bottom: 20px;
        }

        .btn-add, .btn-submit, .btn-back {
            background: var(--accent);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn-back { background: #666; margin-right: 10px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        th { background: #f8f9fa; color: #666; font-size: 13px; }

        /* Mobile */
        @media (max-width: 768px) {
            nav { position: fixed; left: -100px; height: 100%; }
            nav.mobile-open { left: 0; width: 260px; }
            nav.mobile-open .nav-item span { opacity: 1; }
        }
    </style>
</head>
<body>

    <nav id="adminSidebar">
        <div class="nav-header">ZACK ADMIN</div>
        <div class="nav-links">
            <div class="nav-item active" data-target="dashView"><i>📊</i> <span>Dashboard</span></div>
            <div class="nav-item" data-target="instView"><i>🏫</i> <span>Institutions</span></div>
            <div class="nav-item" data-target="prodView"><i>🛍️</i> <span>Products</span></div>
            <div class="nav-item" data-target="orderView"><i>📦</i> <span>Orders</span></div>
            <div class="nav-item" data-target="reqView"><i>📩</i> <span>Requests</span></div>
            <div class="nav-item" data-target="userView"><i>👥</i> <span>Users</span></div>
        </div>
    </nav>

    <div class="main-container">
        <div class="top-bar">
            <div class="toggle-btn" onclick="toggleNav()">☰</div>
            <div class="admin-profile">
                <span>Zack Management</span>
                <div style="width:35px; height:35px; background:var(--accent); border-radius:50%;"></div>
            </div>
        </div>

        <div id="dashView" class="view-panel active">
            <div class="stat-grid">
                <div class="card"><h3>KSH 42,500</h3><p>Total Revenue</p></div>
                <div class="card"><h3>1,284</h3><p>Total Orders</p></div>
                <div class="card"><h3>18</h3><p>Pending Requests</p></div>
            </div>
            <div class="card">
                <h3>Recent Activity</h3>
                <p>All systems operational. New institution registered: City Academy.</p>
            </div>
        </div>

        <div id="instView" class="view-panel">
            <div id="schoolListContainer">
                <button class="btn-add" onclick="toggleSchoolForm()">+ Add New School</button>
                <div class="card">
                    <table>
                        <thead>
                            <tr><th>School Name</th><th>Level</th><th>Classes</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>St. Peters Academy</td>
                                <td>Secondary</td>
                                <td>Form 1-4</td>
                                <td>Active</td>
                                <td>
                                    <button onclick="viewProductsForSchool('St. Peters Academy')" style="background:var(--primary); color:white; border:none; padding:6px 10px; border-radius:5px; cursor:pointer;">View Products</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="schoolAddForm" class="card" style="display:none;">
                <button class="btn-back" onclick="toggleSchoolForm()">← Back</button>
                <h3>Register New Institution</h3>
                <div class="form-group">
                    <label>School Name</label>
                    <input type="text" id="schoolNameInput">
                </div>
                <div class="form-group">
                    <label>School Level (Select Multiple)</label>
                    <select id="levelSelect" multiple style="height:120px;">
                        <option>Pre Primary</option><option>Primary</option>
                        <option>Junior Secondary</option><option>High School</option>
                        <option>College</option><option>Polytechnic</option><option>University</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Class Selection (Select Multiple)</label>
                    <select id="classSelect" multiple>
                        <option>Pre school classes</option><option>Grade 1 to 6</option>
                        <option>Grade 7 to 9</option><option>Grade 10 to 12</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Campus/Courses (Searchable)</label>
                    <input type="text" id="courseSearch" onkeyup="filterCourses()" placeholder="Search courses...">
                    <select id="courseSelect" multiple style="height:120px; margin-top:5px;">
                        <option>Computer Science</option><option>Medicine</option>
                        <option>Mechanical Engineering</option><option>Business Admin</option>
                        <option>Nursing</option><option>Law</option><option>Architecture</option>
                    </select>
                </div>
                <button class="btn-submit" onclick="toggleSchoolForm()">Add Institution</button>
            </div>

            <div id="schoolProductContainer" class="card" style="display:none;">
                <button class="btn-back" onclick="hideProductsForSchool()">← Back</button>
                <h3 id="targetSchoolTitle">Products for School</h3>
                <table>
                    <thead>
                        <tr><th>Product Name</th><th>Price</th><th>Action</th></tr>
                    </thead>
                    <tbody id="schoolProductList">
                        <tr>
                            <td>School Blazer</td>
                            <td>KSH 560</td>
                            <td><button onclick="openLocalUpdate('School Blazer')" style="color:blue; border:none; background:none; font-weight:bold; cursor:pointer;">Update</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="localUpdateForm" class="card" style="display:none;">
                <button class="btn-back" onclick="closeLocalUpdate()">← Back</button>
                <h3 id="localUpdateLabel">Update Product</h3>
                <div class="form-group"><label>Product Name</label><input type="text" value="School Blazer" disabled></div>
                <div class="form-group"><label>Local Price (KSH)</label><input type="number"></div>
                <div class="form-group"><label>Stock Quantity</label><input type="number"></div>
                <button class="btn-submit" onclick="closeLocalUpdate()">Update Only for this School</button>
            </div>
        </div>

        <div id="prodView" class="view-panel">
            <div id="mainProdList">
                <button class="btn-add" onclick="toggleMainProductForm()">+ Add Product</button>
                <div class="stat-grid">
                    <div class="card"><h4>Blazer Set</h4><p>Price:KSH 560</p></div>
                    <div class="card"><h4>PE Kit</h4><p>Price:KSH 600</p></div>
                </div>
            </div>

            <div id="mainProductForm" class="card" style="display:none;">
                <button class="btn-back" onclick="toggleMainProductForm()">← Back</button>
                <h3>Add New Product</h3>
                <div class="form-group"><label>Product Image (Local Storage)</label><input type="file" accept="image/*"></div>
                <div class="form-group"><label>Product Name</label><input type="text"></div>
                <div class="form-group"><label>Description</label><textarea rows="3"></textarea></div>
                <div class="form-group"><label>Base Price</label><input type="number"></div>
                <div class="form-group"><label>Initial Stock</label><input type="number"></div>
                <hr>
                <div class="form-group">
                    <label>Visible to Schools</label>
                    <select multiple><option>St. Peters Academy</option><option>Green Valley</option></select>
                </div>
                <div class="form-group">
                    <label>Target Levels</label>
                    <select multiple><option>Primary</option><option>Secondary</option></select>
                </div>
                <button class="btn-submit">Add Product Globally</button>
            </div>
        </div>

        <div id="orderView" class="view-panel">
            <div class="card">
                <h3>All Orders</h3>
                <table>
                    <tr><th>ID</th><th>Client</th><th>Status</th><th>Date</th></tr>
                    <tr><td>#001</td><td>John Parent</td><td><span style="color:green">Delivered</span></td><td>04 April</td></tr>
                </table>
            </div>
        </div>

        <div id="reqView" class="view-panel">
            <div class="card">
                <h3>Quotation Requests</h3>
                <table>
                    <tr><th>Institution</th><th>Items</th><th>Action</th></tr>
                    <tr><td>Highland School</td><td>100 Full Kits</td><td><button>Respond</button></td></tr>
                </table>
            </div>
        </div>

        <div id="userView" class="view-panel">
            <div class="stat-grid">
                <div class="card">
                    <h3>User Growth (Live)</h3>
                    <canvas id="growthChart" height="200"></canvas>
                </div>
                <div class="card">
                    <h3>Registered Roles</h3>
                    <canvas id="roleChart" height="200"></canvas>
                </div>
            </div>
            <div class="card">
                <h3>Registered Users</h3>
                <table>
                    <tr><th>Name</th><th>Role</th><th>Email</th></tr>
                    <tr><td>Admin One</td><td>Super Admin</td><td>admin@zack.com</td></tr>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle
        function toggleNav() {
            const nav = document.getElementById('adminSidebar');
            if (window.innerWidth <= 768) {
                nav.classList.toggle('mobile-open');
            } else {
                nav.classList.toggle('expanded');
            }
        }

        // Navigation switching logic
        const navItems = document.querySelectorAll('.nav-item');
        const panels = document.querySelectorAll('.view-panel');

        navItems.forEach(item => {
            item.addEventListener('click', () => {
                navItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                const targetId = item.getAttribute('data-target');
                panels.forEach(p => p.classList.remove('active'));
                document.getElementById(targetId).classList.add('active');
                if (targetId === 'userView') renderCharts();
                if (window.innerWidth <= 768) document.getElementById('adminSidebar').classList.remove('mobile-open');
            });
        });

        // School Section Logic
        function toggleSchoolForm() {
            const list = document.getElementById('schoolListContainer');
            const form = document.getElementById('schoolAddForm');
            list.style.display = list.style.display === 'none' ? 'block' : 'none';
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function filterCourses() {
            const val = document.getElementById('courseSearch').value.toUpperCase();
            const opts = document.getElementById('courseSelect').options;
            for (let i = 0; i < opts.length; i++) {
                opts[i].style.display = opts[i].text.toUpperCase().includes(val) ? "block" : "none";
            }
        }

        function viewProductsForSchool(name) {
            document.getElementById('schoolListContainer').style.display = 'none';
            document.getElementById('schoolProductContainer').style.display = 'block';
            document.getElementById('targetSchoolTitle').innerText = "Products for " + name;
        }

        function hideProductsForSchool() {
            document.getElementById('schoolListContainer').style.display = 'block';
            document.getElementById('schoolProductContainer').style.display = 'none';
        }

        function openLocalUpdate(name) {
            document.getElementById('schoolProductContainer').style.display = 'none';
            document.getElementById('localUpdateForm').style.display = 'block';
            document.getElementById('localUpdateLabel').innerText = "Update " + name + " (This School Only)";
        }

        function closeLocalUpdate() {
            document.getElementById('localUpdateForm').style.display = 'none';
            document.getElementById('schoolProductContainer').style.display = 'block';
        }

        // Global Product Logic
        function toggleMainProductForm() {
            const list = document.getElementById('mainProdList');
            const form = document.getElementById('mainProductForm');
            list.style.display = list.style.display === 'none' ? 'block' : 'none';
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        // Live Analytics
        let chartsCreated = false;
        function renderCharts() {
            if (chartsCreated) return;
            chartsCreated = true;
            
            new Chart(document.getElementById('growthChart'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                    datasets: [{ label: 'Users', data: [10, 25, 45, 90], borderColor: '#f68b1e', fill: false }]
                }
            });

            new Chart(document.getElementById('roleChart'), {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Schools', 'Parents'],
                    datasets: [{ label: 'Count', data: [5, 20, 150], backgroundColor: ['#1a237e', '#f68b1e', '#0d1440'] }]
                }
            });
        }
    </script>
</body>
</html>
