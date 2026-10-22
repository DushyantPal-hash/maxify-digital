<?php
/**
 * Maxify Digital - Salesforce Partner Service Page
 * 
 * Active SEO & AI Search Optimization
 * Primary Keyword: Salesforce Partner
 * Secondary Keywords: Salesforce Consulting Partner, Certified Salesforce Partner,
 * Salesforce Implementation Partner, Salesforce Development Partner, Salesforce Consulting Services,
 * Salesforce CRM Consulting, Salesforce Integration Partner, Salesforce Managed Services, Salesforce Agentforce Partner
 */

// Configuration Variables for Easy Editing & Maintenance
$companyName = "Maxify Digital";
$primaryKeyword = "Salesforce Partner";
$seoTitle = "Certified Salesforce Partner | Salesforce Consulting & Implementation Services | Maxify Digital";
$metaDescription = "Maxify Digital is a certified Salesforce Partner offering Salesforce consulting, implementation, development, integration, migration, and managed services to help businesses accelerate growth and digital transformation.";
$canonicalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Helper functions for IP & Masking
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function mask_email($email) {
    $parts = explode("@", $email);
    if (count($parts) < 2) return $email;
    $name = substr($parts[0], 0, 1) . str_repeat("*", max(strlen($parts[0]) - 2, 1)) . substr($parts[0], -1);
    return $name . "@" . $parts[1];
}

function mask_phone($phone) {
    if (empty($phone)) return "";
    if (substr($phone, 0, 1) == '+') {
        preg_match('/^\+\d{1,3}/', $phone, $matches); 
        $countryCode = $matches[0] ?? '';
        $localNumber = substr($phone, strlen($countryCode));
        $maskedLocal = str_repeat("*", max(strlen($localNumber) - 2, 1)) . substr($localNumber, -2);
        return $countryCode . $maskedLocal;
    } else {
        return substr($phone, 0, 2) . str_repeat("*", max(strlen($phone) - 4, 1)) . substr($phone, -2);
    }
}

// Lead Capture / Form Submission
$submissionSuccess = false;
$submissionError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_lead'])) {
    // Honeypot check
    if (!empty($_POST['website'])) {
        $submissionError = "Spam detected";
    } else {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_SPECIAL_CHARS);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
        $service = filter_input(INPUT_POST, 'service', FILTER_SANITIZE_SPECIAL_CHARS);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($name) || !$email || empty($service)) {
            $submissionError = "Please complete all required fields (Name, Email, and Service).";
        } else {
            try {
                $ip = get_user_ip();
                $timestamp = date("Y-m-d H:i:s");
                $page = isset($_SERVER['HTTP_REFERER']) ? basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH)) : 'salesforce-consulting-partner.php';

                $full_message = "
                <html>
                <head>
                <title>New Salesforce Inquiry</title>
                </head>
                <body>
                <h3>New Salesforce Inquiry (Website Form)</h3>
                <p><b>Name:</b> $name</p>
                <p><b>Company Name:</b> $company</p>
                <p><b>Email Address:</b> $email</p>
                <p><b>Phone Number:</b> $phone</p>
                <p><b>Requested Service:</b> $service</p>
                <p><b>Message:</b> $message</p>
                <p><b>IP Address:</b> $ip</p>
                <p><b>Submitted On:</b> $timestamp</p>
                <p><b>Source Page:</b> $page</p>
                </body>
                </html>
                ";

                $masked_message = "
                <html>
                <head>
                <title>New Salesforce Inquiry - CC</title>
                </head>
                <body>
                <h3>New Salesforce Inquiry (Website Form) - CC</h3>
                <p><b>Name:</b> $name</p>
                <p><b>Company Name:</b> $company</p>
                <p><b>Email Address:</b> " . mask_email($email) . "</p>
                <p><b>Phone Number:</b> " . ($phone ? mask_phone($phone) : 'N/A') . "</p>
                <p><b>Requested Service:</b> $service</p>
                <p><b>Message:</b> $message</p>
                <p><b>IP Address:</b> $ip</p>
                <p><b>Submitted On:</b> $timestamp</p>
                <p><b>Source Page:</b> $page</p>
                </body>
                </html>
                ";

                // Native mail() headers
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                $headers .= 'From: Maxify Digital <success@maxify.digital>' . "\r\n";
                $headers .= 'Reply-To: ' . $email . "\r\n";
                $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

                // Send first email to primary recipient
                $subject1 = "New Salesforce Partner Inquiry: $service";
                $sent1 = mail('info@hashstudioz.com', $subject1, $full_message, $headers, '-f success@maxify.digital');

                // Send second email to CC recipient with masking
                $subject2 = "New Salesforce Partner Inquiry (CC): $service";
                $sent2 = mail('amrish.kumar@hashstudioz.com', $subject2, $masked_message, $headers, '-f success@maxify.digital');

                if ($sent1 && $sent2) {
                    $submissionSuccess = true;
                } else {
                    $submissionError = "Mail sending failed via server mail function.";
                }
            } catch (Exception $e) {
                $submissionError = "An error occurred while sending mail: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
    
    <!-- Open Graph Meta Tags for Social Media Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:image" content="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&h=630&q=80">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">

    <!-- Google Fonts & Tailwind CSS CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sfBlue: '#0176D3',
                        sfBlueHover: '#015ba3',
                        sfNavy: '#032D60',
                        sfDark: '#101216',
                        sfMuted: '#6B7280',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: radial-gradient(circle at 10% 20%, rgba(1, 118, 211, 0.08) 0%, rgba(255, 255, 255, 0) 60%);
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans antialiased selection:bg-sfBlue/20">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl font-bold font-display text-sfNavy flex items-center gap-2">
                    <svg class="w-8 h-8 text-sfBlue" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/>
                    </svg>
                    Maxify<span class="text-sfBlue">Digital</span>
                </span>
                <span class="hidden md:inline-block px-2.5 py-1 text-xs font-semibold text-sfBlue bg-sfBlue/10 rounded-full">
                    Official Salesforce Partner
                </span>
            </div>
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                <a href="#benefits" class="hover:text-sfBlue transition-colors">Why Us</a>
                <a href="#services" class="hover:text-sfBlue transition-colors">Services</a>
                <a href="#clouds" class="hover:text-sfBlue transition-colors">Clouds</a>
                <a href="#faq" class="hover:text-sfBlue transition-colors">FAQ</a>
            </nav>
            <div>
                <a href="#consultation" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-sfBlue hover:bg-sfBlueHover transition-all shadow-md shadow-sfBlue/10 hover:shadow-lg">
                    Book Free Consultation
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content wrapper -->
    <main class="relative overflow-hidden">
        
        <!-- Hero Section -->
        <section class="gradient-bg pt-12 pb-20 md:pt-20 md:pb-32 border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Hero Content -->
                    <div class="lg:col-span-7 space-y-8">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sfBlue/5 border border-sfBlue/20 text-sfBlue text-xs font-semibold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-sfBlue animate-pulse"></span>
                            Certified Salesforce Partner
                        </div>
                        
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold font-display text-sfNavy tracking-tight leading-tight">
                            Certified Salesforce Partner for Consulting, Implementation & Growth
                        </h1>
                        
                        <p class="text-lg text-slate-600 leading-relaxed max-w-2xl">
                            Maxify Digital is a trusted <span class="font-semibold text-slate-900">Salesforce Partner</span> helping businesses streamline sales, automate workflows, improve customer experiences, and scale operations with tailored Salesforce solutions. From Salesforce consulting and implementation to custom development, integrations, migration, and managed services, we deliver end-to-end Salesforce expertise that drives measurable business results.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <a href="#consultation" class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold rounded-lg text-white bg-sfBlue hover:bg-sfBlueHover transition-all shadow-lg shadow-sfBlue/20">
                                Book Free Consultation
                            </a>
                            <a href="#consultation" class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold rounded-lg text-sfNavy bg-white border border-slate-200 hover:border-sfBlue/30 hover:bg-slate-50 transition-all">
                                Talk to Salesforce Experts
                            </a>
                        </div>
                        
                        <!-- Trust points -->
                        <div class="grid grid-cols-3 gap-4 pt-8 border-t border-slate-200">
                            <div>
                                <div class="text-xs font-bold text-sfNavy uppercase tracking-wider">Certified</div>
                                <div class="text-sm font-medium text-slate-500">Salesforce Experts</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-sfNavy uppercase tracking-wider">End-to-End</div>
                                <div class="text-sm font-medium text-slate-500">Salesforce Services</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-sfNavy uppercase tracking-wider">AppExchange</div>
                                <div class="text-sm font-medium text-slate-500">Listed Partner</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Card Graphic / Interactive Lead Capture preview -->
                    <div class="lg:col-span-5">
                        <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-xl relative">
                            <div class="absolute -top-4 -right-4 bg-sfBlue text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                Active Partnership
                            </div>
                            
                            <h3 class="text-xl font-bold text-sfNavy font-display mb-2">Maximize Salesforce ROI</h3>
                            <p class="text-sm text-slate-500 mb-6">Connect with our consultants for a detailed systems assessment.</p>

                            <?php if ($submissionSuccess): ?>
                                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-6 rounded-xl text-center space-y-4">
                                    <svg class="w-12 h-12 text-emerald-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <h4 class="text-lg font-bold">Consultation Booked!</h4>
                                    <p class="text-sm text-emerald-700">Thank you, <?php echo htmlspecialchars($name); ?>. Our Salesforce consultants will reach out to you within 24 hours.</p>
                                </div>
                            <?php else: ?>
                                <form action="salesforce-consulting-partner.php#consultation" method="POST" class="space-y-4">
                                    <!-- Honeypot spam protection hidden field -->
                                    <input type="text" name="website" id="form-website" style="display:none">

                                    <?php if (!empty($submissionError)): ?>
                                        <div class="p-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg">
                                            <?php echo $submissionError; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1" for="name">Full Name *</label>
                                        <input type="text" id="name" name="name" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sfBlue/20 focus:border-sfBlue transition-all" placeholder="John Doe">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1" for="email">Business Email *</label>
                                            <input type="email" id="email" name="email" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sfBlue/20 focus:border-sfBlue transition-all" placeholder="john@company.com">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1" for="phone">Phone Number</label>
                                            <input type="text" id="phone" name="phone" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sfBlue/20 focus:border-sfBlue transition-all" placeholder="+1 (555) 000-0000">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1" for="company">Company Name</label>
                                        <input type="text" id="company" name="company" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sfBlue/20 focus:border-sfBlue transition-all" placeholder="Acme Corp">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1" for="service">Requested Salesforce Service *</label>
                                        <select id="service" name="service" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sfBlue/20 focus:border-sfBlue transition-all">
                                            <option value="">-- Select Salesforce Service --</option>
                                            <option value="consulting">Salesforce CRM Consulting</option>
                                            <option value="implementation">Salesforce Implementation Partner</option>
                                            <option value="development">Salesforce Development Partner</option>
                                            <option value="integration">Salesforce Integration Partner</option>
                                            <option value="managed_services">Salesforce Managed Services</option>
                                            <option value="agentforce">Salesforce Agentforce Partner</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1" for="message">How can we help?</label>
                                        <textarea id="message" name="message" rows="2" class="w-full px-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sfBlue/20 focus:border-sfBlue transition-all" placeholder="Briefly describe your objectives..."></textarea>
                                    </div>
                                    <button type="submit" name="submit_lead" class="w-full py-3 px-4 bg-sfBlue hover:bg-sfBlueHover text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all">
                                        Schedule Consultation
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Why Businesses Choose a Salesforce Partner Section -->
        <section id="benefits" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center space-y-4 mb-16">
                    <h2 class="text-3xl font-extrabold font-display text-sfNavy tracking-tight sm:text-4xl">
                        Why Businesses Choose a Salesforce Partner
                    </h2>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        Implementing Salesforce successfully requires more than software—it requires strategy, customization, integration, user adoption, and ongoing optimization. As a trusted <span class="font-semibold text-sfBlue">Salesforce Consulting Partner</span>, Maxify Digital helps organizations successfully navigate this journey.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Cards representing the business outcomes -->
                    <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-sfBlue/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sfBlue/10 text-sfBlue flex items-center justify-center mb-5 group-hover:bg-sfBlue group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-sfNavy mb-2">Increase Sales Productivity</h3>
                        <p class="text-sm text-slate-500">Eliminate repetitive operational work, streamline lead flows, and empower sales reps to focus on what they do best: closing deals.</p>
                    </div>

                    <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-sfBlue/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sfBlue/10 text-sfBlue flex items-center justify-center mb-5 group-hover:bg-sfBlue group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-sfNavy mb-2">Automate Manual Processes</h3>
                        <p class="text-sm text-slate-500">Set up robust automated approval workflows, pipeline updates, and tasks that slash human error and trigger actions instantly.</p>
                    </div>

                    <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-sfBlue/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sfBlue/10 text-sfBlue flex items-center justify-center mb-5 group-hover:bg-sfBlue group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-sfNavy mb-2">Improve Customer Engagement</h3>
                        <p class="text-sm text-slate-500">Leverage complete 360-degree client timelines to target communications, resolve tickets faster, and cultivate deeper relationships.</p>
                    </div>

                    <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-sfBlue/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sfBlue/10 text-sfBlue flex items-center justify-center mb-5 group-hover:bg-sfBlue group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-sfNavy mb-2">Unify Customer Data</h3>
                        <p class="text-sm text-slate-500">Break down isolated functional departments. Consolidate systems to provide one golden, shared record of truth for every contact.</p>
                    </div>

                    <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-sfBlue/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sfBlue/10 text-sfBlue flex items-center justify-center mb-5 group-hover:bg-sfBlue group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2zm9 0h2a2 2 0 002-2v-3a2 2 0 00-2-2h-2a2 2 0 00-2 2v3a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-sfNavy mb-2">Actionable Insights</h3>
                        <p class="text-sm text-slate-500">Deploy beautiful charts, live tracking lists, and AI intelligence dashboards so executives make confident, data-driven calls.</p>
                    </div>

                    <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-sfBlue/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sfBlue/10 text-sfBlue flex items-center justify-center mb-5 group-hover:bg-sfBlue group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-sfNavy mb-2">Maximize Salesforce ROI</h3>
                        <p class="text-sm text-slate-500">Configure out-of-the-box properties instead of high-cost overrides, driving rapid time-to-market and lower ongoing maintenance.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Salesforce Partner Services Section -->
        <section id="services" class="py-20 bg-slate-50 border-y border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center space-y-4 mb-16">
                    <span class="text-sm font-semibold text-sfBlue uppercase tracking-wider">Services Matrix</span>
                    <h2 class="text-3xl font-extrabold font-display text-sfNavy tracking-tight sm:text-4xl">
                        Our Salesforce Partner Services
                    </h2>
                    <p class="text-slate-600">
                        Maximize operations and build a unified pipeline with specialized end-to-end expertise.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Service 1 -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-md flex gap-6">
                        <div class="hidden sm:flex w-12 h-12 shrink-0 rounded-xl bg-sfBlue/10 text-sfBlue items-center justify-center font-bold text-lg">01</div>
                        <div class="space-y-4">
                            <h3 class="text-xl font-bold text-sfNavy">Salesforce Consulting Services</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                We assess your business processes and create a Salesforce roadmap aligned with your business goals. Our Salesforce CRM consulting evaluates platform gaps and structures actionable transition tracks.
                            </p>
                            <ul class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-500">
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> CRM Strategy</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Sales Process Tuning</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Service Workflow Design</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Automation Planning</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Service 2 -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-md flex gap-6">
                        <div class="hidden sm:flex w-12 h-12 shrink-0 rounded-xl bg-sfBlue/10 text-sfBlue items-center justify-center font-bold text-lg">02</div>
                        <div class="space-y-4">
                            <h3 class="text-xl font-bold text-sfNavy">Salesforce Implementation Services</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                As a premier Salesforce Implementation Partner, we deliver smooth and scalable deployments without affecting your operational stability.
                            </p>
                            <ul class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-500">
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Sales & Service Cloud</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Experience Cloud</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Marketing Cloud</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Data Cloud & Agentforce</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Service 3 -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-md flex gap-6">
                        <div class="hidden sm:flex w-12 h-12 shrink-0 rounded-xl bg-sfBlue/10 text-sfBlue items-center justify-center font-bold text-lg">03</div>
                        <div class="space-y-4">
                            <h3 class="text-xl font-bold text-sfNavy">Salesforce Development Services</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Build custom Salesforce solutions tailored to your business model. As an expert Salesforce Development Partner, we extend out-of-the-box configurations into powerful customized flows.
                            </p>
                            <ul class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-500">
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Apex & Visualforce</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Lightning Web Components</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Custom Dashboard Systems</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> AppExchange App Dev</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Service 4 -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-md flex gap-6">
                        <div class="hidden sm:flex w-12 h-12 shrink-0 rounded-xl bg-sfBlue/10 text-sfBlue items-center justify-center font-bold text-lg">04</div>
                        <div class="space-y-4">
                            <h3 class="text-xl font-bold text-sfNavy">Salesforce Integration Services</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Connect Salesforce seamlessly with your existing critical tech ecosystem. Our expertise as a Salesforce Integration Partner bridges isolated channels easily.
                            </p>
                            <ul class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-500">
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> ERP & Accounting Sync</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> E-Commerce & Marketing</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> API & Gateway Integrations</li>
                                <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-sfBlue"></span> Legacy System Migrations</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Clouds We Support -->
        <section id="clouds" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center space-y-4 mb-16">
                    <span class="text-sm font-semibold text-sfBlue uppercase tracking-wider">Ecosystem Coverage</span>
                    <h2 class="text-3xl font-extrabold font-display text-sfNavy tracking-tight sm:text-4xl">
                        Salesforce Clouds We Support
                    </h2>
                    <p class="text-slate-600">We offer specialized configurations and certified developers across the full suite.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">Sales Cloud</div>
                        <span class="text-xs text-sfBlue font-medium">CRM & Sales Pipeline</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">Service Cloud</div>
                        <span class="text-xs text-sfBlue font-medium">Support ticketing & SLA</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">Marketing Cloud</div>
                        <span class="text-xs text-sfBlue font-medium">Automated Journeys</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">Experience Cloud</div>
                        <span class="text-xs text-sfBlue font-medium">Portals & Communities</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">Data Cloud</div>
                        <span class="text-xs text-sfBlue font-medium">Unified Profiles</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">Agentforce</div>
                        <span class="text-xs text-sfBlue font-medium">AI Agent Support</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">CPQ Cloud</div>
                        <span class="text-xs text-sfBlue font-medium">Configure, Price, Quote</span>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 text-center hover:shadow-md transition-all">
                        <div class="font-bold text-sfNavy text-lg mb-1">Field Service</div>
                        <span class="text-xs text-sfBlue font-medium">On-Site Dispatching</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Industries We Serve -->
        <section class="py-20 bg-slate-50 border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center space-y-4 mb-16">
                    <h2 class="text-3xl font-extrabold font-display text-sfNavy tracking-tight sm:text-4xl">
                        Industries We Serve
                    </h2>
                    <p class="text-slate-600">Tailored data architectures matching unique regulatory and operational needs.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php 
                    $industries = ["Healthcare", "Manufacturing", "Retail & E-commerce", "Financial Services", "Insurance", "Logistics", "Education", "Real Estate"];
                    foreach ($industries as $index => $industry): ?>
                        <div class="bg-white p-5 rounded-xl border border-slate-100 flex items-center gap-3 shadow-sm">
                            <span class="w-7.5 h-7.5 rounded-full bg-sfBlue/10 text-sfBlue flex items-center justify-center text-xs font-bold"><?php echo ($index + 1); ?></span>
                            <span class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($industry); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Why Choose Maxify Digital Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="space-y-6">
                        <span class="text-sm font-semibold text-sfBlue uppercase tracking-wider">Trusted Architecture</span>
                        <h2 class="text-3xl font-extrabold font-display text-sfNavy tracking-tight sm:text-4xl">
                            Why Choose Maxify Digital?
                        </h2>
                        <p class="text-slate-600 leading-relaxed">
                            We bridge software capability and actual executive goals. We build robust systems that respect team workflows, ensure bulletproof data cleanups, and scale seamlessly.
                        </p>
                        
                        <div class="space-y-4 pt-4">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">✓</div>
                                <div>
                                    <h4 class="font-bold text-slate-900">Official Salesforce Partner</h4>
                                    <p class="text-xs text-slate-500">AppExchange listed consulting partner complying with global certification parameters.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">✓</div>
                                <div>
                                    <h4 class="font-bold text-slate-900">Certified Salesforce Professionals</h4>
                                    <p class="text-xs text-slate-500">An in-house crew of certified system architects, Salesforce consulting partner strategists, and LWC developers.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">✓</div>
                                <div>
                                    <h4 class="font-bold text-slate-900">End-to-End Delivery</h4>
                                    <p class="text-xs text-slate-500">Direct hands-on involvement through roadmap, architecture design, customization, deployment, and ongoing Salesforce Managed Services.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">✓</div>
                                <div>
                                    <h4 class="font-bold text-slate-900">Business-Focused ROI</h4>
                                    <p class="text-xs text-slate-500">We optimize for speed, usability, and lower custom overhead to drive active system adoption and positive bottom lines.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative bg-gradient-to-br from-sfNavy to-sfDark p-8 rounded-2xl text-white space-y-8 overflow-hidden shadow-xl">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-sfBlue/10 rounded-full blur-3xl"></div>
                        <h3 class="text-2xl font-bold font-display">Active AppExchange Listing</h3>
                        <p class="text-sm text-slate-300">
                            Our team works in active collaboration with regional Salesforce product architects, granting us early-stage access to sandbox features, Data Cloud pilots, and CPQ engines.
                        </p>
                        <div class="border-t border-slate-700/60 pt-6 grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-2xl font-bold text-sfBlue">15+</div>
                                <div class="text-xs text-slate-400">Certified Experts</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-sfBlue">98%</div>
                                <div class="text-xs text-slate-400">Client CSAT Score</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Frequently Asked Questions -->
        <section id="faq" class="py-20 bg-slate-50 border-y border-slate-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div class="text-center space-y-4 mb-16">
                    <h2 class="text-3xl font-extrabold font-display text-sfNavy tracking-tight">
                        Frequently Asked Questions
                    </h2>
                    <p class="text-slate-600">Answers to key strategic questions regarding Salesforce Partnerships.</p>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                        <h4 class="font-bold text-sfNavy text-base mb-2">What does a Salesforce Partner do?</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            A Salesforce Partner helps businesses implement, customize, integrate, and optimize Salesforce solutions. We translate business workflows into Salesforce configurations, handle migrations, connect legacy databases, and train staff to drive maximum platform adoption.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                        <h4 class="font-bold text-sfNavy text-base mb-2">Why should I work with a certified Salesforce Partner?</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Certified partners bring verified expertise, experience from previous multi-system rollouts, and direct contact with Salesforce support structures. This reduces deployment failure risks, saves time, and prevents expensive platform customizations when standard structures fit.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm">
                        <h4 class="font-bold text-sfNavy text-base mb-2">Do you provide ongoing Salesforce support?</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Yes. We provide continuous support under our Salesforce Managed Services. This includes daily platform administration, custom dashboards, adding objects/fields, configuring new flows, user training, and keeping systems tuned for periodic Salesforce product updates.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer Lead Call-to-Action Section -->
        <section id="consultation" class="py-20 bg-gradient-to-br from-sfNavy to-sfDark text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-sfBlue/20 to-transparent"></div>
            <div class="max-w-5xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-8">
                <h2 class="text-4xl font-extrabold font-display tracking-tight sm:text-5xl">
                    Ready to Accelerate Growth with Salesforce?
                </h2>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    Partner with Maxify Digital to transform your CRM, automate operations, and create exceptional customer experiences. Let our certified Salesforce Consulting experts design your optimal path.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                    <a href="#consultation" class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold rounded-lg text-white bg-sfBlue hover:bg-sfBlueHover transition-all shadow-lg shadow-sfBlue/20">
                        Schedule a Free Consultation
                    </a>
                    <a href="#consultation" class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold rounded-lg text-white bg-transparent border border-slate-600 hover:border-slate-400 hover:bg-white/5 transition-all">
                        Contact Salesforce Experts
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Area -->
    <footer class="bg-[#101216] border-t border-slate-800/80 py-12 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <p>&copy; <?php echo date('Y'); ?> Maxify Digital. All rights reserved. Registered Salesforce Consulting Partner.</p>
                <p class="text-xs text-slate-600 mt-1">Salesforce, Sales Cloud, Service Cloud, Marketing Cloud, Experience Cloud, Data Cloud, and Agentforce are registered trademarks of Salesforce, Inc.</p>
            </div>
            <div class="flex gap-6 text-xs uppercase tracking-wider font-semibold">
                <a href="#benefits" class="hover:text-white transition-colors">Benefits</a>
                <a href="#services" class="hover:text-white transition-colors">Services</a>
                <a href="#clouds" class="hover:text-white transition-colors">Clouds</a>
                <a href="#faq" class="hover:text-white transition-colors">FAQ</a>
            </div>
        </div>
    </footer>

</body>
</html>