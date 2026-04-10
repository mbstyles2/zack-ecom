<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Register</title>
    <style>
        :root {
            --blue: #2563eb;
            --orange: #f97316;
            --light-bg: #f8fafc;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            width: 100%;
            max-width: 450px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Navigation Tabs */
        .tabs {
            display: flex;
            background: #fff;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .tab {
            flex: 1;
            padding: 20px 0;
            text-align: center;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9rem;
            color: #64748b;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tab.active { color: var(--blue); }
        .tab:nth-child(2).active { color: var(--orange); }

        .indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            width: 50%;
            background: var(--blue);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 4px 4px 0 0;
        }

        /* Form Container */
        .slider-container {
            width: 100%;
            overflow: hidden;
        }

        .slider-inner {
            display: flex;
            width: 200%; /* Two main categories: Institution and Parent/Student */
            transition: 0.5s ease-in-out;
        }

        section {
            width: 50%;
            padding: 30px;
            box-sizing: border-box;
        }

        /* Inputs */
        .field { margin-bottom: 18px; position: relative; }

        label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 6px;
            font-weight: 600;
            color: #1e293b;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: 0.2s;
            background-color: var(--light-bg);
            box-sizing: border-box;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--blue);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* Password Toggle */
        .pass-container { position: relative; }
        
        .toggle-btn {
            position: absolute;
            right: 14px;
            top: 38px;
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 0;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .orange-theme .btn-submit { background: var(--orange); box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.2); }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .footer a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="tabs">
        <div class="tab active" onclick="slide(0)">Institution</div>
        <div class="tab" onclick="slide(1)">Parent / Student</div>
        <div class="indicator" id="indicator"></div>
    </div>

    <div class="slider-container">
        <div class="slider-inner" id="sliderInner">
            
            <section>
                <div class="field">
                    <label>Institution Name</label>
                    <input type="text" placeholder="e.g. Nairobi High School">
                </div>
                                <div class="field">
                    <label>Official Email</label>
                    <input type="email" placeholder="admin@school.ac.ke">
                </div>
                                <div class="field">
                    <label>Phone Number</label>
                    <input type="number" placeholder="0716.....">
                </div>
                                <div class="field">
                    <label>P.O.Box</label>
                    <input type="text" placeholder="100-....">
                </div>
                <div class="field">
                    <label>County</label>
                    <select class="county-list">
                        <option value="">Select County...</option>
                        </select>
                </div>
                <div class="field">
                    <label>School Code</label>
                    <input type="number" placeholder="(must for high schools and below)">
                </div>
                <div class="field pass-container">
                    <label> Create Password</label>
                    <input type="password" class="pass-input">
                    <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                </div>
                                <div class="field pass-container">
                    <label>Confirm Password</label>
                    <input type="password" class="pass-input">
                    <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                </div>
                <button class="btn-submit">Register Institution</button>
            </section>

            <section class="orange-theme">
                <div class="field">
                    <label>I am a:</label>
                    <select id="userRole">
                        <option value="parent">Parent / Guardian</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" placeholder="Enter your official name">
                </div>
                <div class="field">
                    <label>Institution Name</label>
                    <input type="txt" placeholder="chavakali senior school">
                </div>
                
                <div class="field">
                    <label>Phone Number</label>
                    <input type="tel" placeholder="0712 345 678">
                </div>
                <div class="field">
                    <label>County of Residence</label>
                    <select class="county-list">
                        <option value="">Select County...</option>
                    </select>
                </div>
                <div class="field pass-container">
                    <label>Create Password</label>
                    <input type="password" class="pass-input">
                    <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                </div>
                                <div class="field pass-container">
                    <label>Confirm Password</label>
                    <input type="password" class="pass-input">
                    <button type="button" class="toggle-btn" onclick="togglePass(this)">👁️</button>
                </div>
                <button class="btn-submit">Create Account</button>
            </section>

        </div>
    </div>

    <div class="footer">
        Already have an account? <a href="login.html">Login here</a>
    </div>
</div>

<script>
    const counties = [
        "Mombasa", "Kwale", "Kilifi", "Tana River", "Lamu", "Taita-Taveta", "Garissa", "Wajir", "Mandera", "Marsabit", 
        "Isiolo", "Meru", "Tharaka-Nithi", "Embu", "Kitui", "Machakos", "Makueni", "Nyandarua", "Nyeri", "Kirinyaga", 
        "Murang'a", "Kiambu", "Turkana", "West Pokot", "Samburu", "Trans-Nzoia", "Uasin Gishu", "Elgeyo-Marakwet", "Nandi", "Baringo", 
        "Laikipia", "Nakuru", "Narok", "Kajiado", "Kericho", "Bomet", "Kakamega", "Vihiga", "Bungoma", "Busia", 
        "Siaya", "Kisumu", "Homa Bay", "Migori", "Kisii", "Nyamira", "Nairobi"
    ];

    // Populate all county dropdowns
    document.querySelectorAll('.county-list').forEach(select => {
        counties.sort().forEach(county => {
            let opt = document.createElement('option');
            opt.value = county.toLowerCase();
            opt.innerHTML = county;
            select.appendChild(opt);
        });
    });

    // Slider Logic
    function slide(index) {
        const inner = document.getElementById('sliderInner');
        const indicator = document.getElementById('indicator');
        const tabs = document.querySelectorAll('.tab');

        inner.style.transform = `translateX(-${index * 50}%)`;
        indicator.style.left = `${index * 50}%`;
        
        tabs.forEach(t => t.classList.remove('active'));
        tabs[index].classList.add('active');

        // Change indicator color based on section
        indicator.style.background = (index === 1) ? 'var(--orange)' : 'var(--blue)';
    }

    // Toggle Password Visibility
    function togglePass(btn) {
        const input = btn.parentElement.querySelector('.pass-input');
        if (input.type === "password") {
            input.type = "text";
            btn.innerHTML = "🙈";
        } else {
            input.type = "password";
            btn.innerHTML = "👁️";
        }
    }
</script>

</body>
</html>
