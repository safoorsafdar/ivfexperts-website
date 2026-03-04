<?php

// Core Site Info
$siteName = "IVF Experts Pakistan";
$baseUrl = "https://ivfexperts.pk";
$currentUrl = $baseUrl . $_SERVER['REQUEST_URI'];
$uriPaths = trim($_SERVER['REQUEST_URI'], "/");

// Default SEO Values (Aggressive Pakistan Targeting)
$pageTitle = "IVF & Fertility Specialist in Lahore, Pakistan | Dr. Adnan Jabbar";
$metaDescription = "Top-rated IVF Specialist and Clinical Embryologist in Lahore. High success rates for IVF, ICSI, Gender Selection, and Infertility treatments across Pakistan.";
$ogImage = "https://ivfexperts.pk/assets/images/art_procedures_lab.png"; // Default OG image

// Structured Data Variables
$schemaType = "MedicalWebPage";
$medicalSpecialty = "Reproductive Medicine";

// ==========================================
// DYNAMIC PAGE-SPECIFIC SEO ROUTING
// ==========================================

// Homepage
if (empty($uriPaths) || $uriPaths == 'index.php') {
    $pageTitle = "IVF & Fertility Specialist in Lahore, Pakistan | Dr. Adnan Jabbar";
    $metaDescription = "Looking for the best IVF specialist in Lahore? Dr. Adnan Jabbar offers world-class IVF, ICSI, PGT Gender Selection, and Male Infertility treatments in Pakistan.";
}
// About
elseif (strpos($uriPaths, 'about') !== false) {
    $pageTitle = "About Dr. Adnan Jabbar | Top Fertility Consultant in Pakistan";
    $metaDescription = "Meet Dr. Adnan Jabbar, a dual-trained Fertility Consultant and Clinical Embryologist serving Lahore, Karachi, Islamabad, and all of Pakistan with ethical IVF care.";
    $ogImage = "https://ivfexperts.pk/assets/images/dr-adnan.jpg";
}
// Contact
elseif (strpos($uriPaths, 'contact') !== false) {
    $pageTitle = "Contact IVF Experts Lahore | Book Fertility Consultation";
    $metaDescription = "Schedule an appointment with the best IVF specialist in Lahore. Clinics located in Lahore, Okara, serving patients from Islamabad, Karachi, and Multan.";
}

// ==== MALE INFERTILITY ====
elseif (strpos($uriPaths, 'male-infertility') !== false) {
    $schemaType = "MedicalCondition";
    if (strpos($uriPaths, 'low-sperm-count') !== false) {
        $pageTitle = "Low Sperm Count Treatment in Pakistan | Oligospermia Experts";
        $metaDescription = "Best treatment for low sperm count (Oligospermia) in Lahore. Advanced hormonal, surgical, and ICSI protocols for male infertility in Pakistan.";
    }
    elseif (strpos($uriPaths, 'azoospermia') !== false) {
        $pageTitle = "Azoospermia Treatment (Zero Sperm) in Lahore, Pakistan | Micro-TESE";
        $metaDescription = "Successful Azoospermia (zero sperm) treatment in Pakistan. Dr. Adnan Jabbar specializes in Micro-TESE and ICSI for severe male infertility.";
    }
    elseif (strpos($uriPaths, 'varicocele') !== false) {
        $pageTitle = "Varicocele Repair & Male Infertility Treatment in Pakistan";
        $metaDescription = "Expert Varicocele diagnosis and treatment in Lahore. Improve sperm count, motility, and morphology naturally or through advanced microsurgery.";
    }
    elseif (strpos($uriPaths, 'dna-fragmentation') !== false) {
        $pageTitle = "Sperm DNA Fragmentation Testing & Treatment | Lahore, Pakistan";
        $metaDescription = "High Sperm DNA Fragmentation causes recurrent miscarriages and IVF failure. Get advanced testing and targeted PICSI/ICSI treatments in Lahore.";
    }
    elseif (strpos($uriPaths, 'erectile-ejaculatory-dysfunction') !== false) {
        $pageTitle = "Erectile & Ejaculatory Dysfunction Fertility Treatment Pakistan";
        $metaDescription = "Confidential, advanced treatments for retrograde ejaculation and ED in Lahore. Overcome mechanical male infertility with expert sperm retrieval techniques.";
    }
    elseif (strpos($uriPaths, 'unexplained-male-infertility') !== false) {
        $pageTitle = "Unexplained Male Infertility Treatment in Lahore, Pakistan";
        $metaDescription = "Normal semen analysis but still struggling? Discover advanced diagnostics like Reactive Oxygen Species (ROS) testing and PICSI for unexplained male infertility.";
    }
    elseif (strpos($uriPaths, 'klinefelters-syndrome') !== false) {
        $pageTitle = "Klinefelter Syndrome Fertility Treatment | Micro-TESE Lahore";
        $metaDescription = "Compassionate, cutting-edge fertility treatments for Klinefelter's Syndrome. Specialized Micro-TESE surgical sperm retrieval by Dr. Adnan Jabbar in Pakistan.";
    }
    elseif (strpos($uriPaths, 'hypogonadotropic-hypogonadism') !== false) {
        $pageTitle = "Hypogonadotropic Hypogonadism Fertility Treatment | Lahore";
        $metaDescription = "Expert hormonal therapy and fertility treatment for Hypogonadotropic Hypogonadism in Pakistan. Dr. Adnan Jabbar restores male fertility and testosterone.";
    }
    elseif (strpos($uriPaths, 'low-testicular-volume') !== false) {
        $pageTitle = "Low Testicular Volume & Testicular Atrophy Treatment Pakistan";
        $metaDescription = "Advanced male infertility treatments for low testicular volume and atrophy in Lahore. Maximize sperm production with targeted therapies and Micro-TESE.";
    }
    elseif (strpos($uriPaths, 'primary-testicular-failure') !== false) {
        $pageTitle = "Primary Testicular Failure Treatment | Lahore, Pakistan";
        $metaDescription = "Hope for Primary Testicular Failure (Hypergonadotropic Hypogonadism). Advanced Micro-TESE and regenerative stem cell options in Pakistan.";
    }
    elseif (strpos($uriPaths, 'testicular-recovery-stemcell') !== false) {
        $pageTitle = "Testicular Recovery via Stem Cell Therapy | Pakistan Infertility";
        $metaDescription = "Pioneering stem cell therapy for testicular regeneration and non-obstructive azoospermia in Lahore, Pakistan. Leading-edge male infertility treatment.";
    }
    elseif (strpos($uriPaths, 'penile-doppler-ultrasound') !== false) {
        $pageTitle = "Penile Doppler Ultrasound (PSV, EDV, RI) in Lahore, Pakistan";
        $metaDescription = "Expert Penile Doppler Ultrasound for erectile dysfunction. Measuring PSV, EDV, and RI per AUA/EAU guidelines with Dr. Adnan Jabbar in Lahore.";
    }
    else {
        $pageTitle = "Male Infertility Specialist in Lahore, Pakistan | Dr. Adnan Jabbar";
        $metaDescription = "Leading male infertility expert in Lahore. Treating low sperm count, zero sperm, erectile dysfunction, and offering elite ICSI solutions across Pakistan.";
    }
}

// ==== FEMALE INFERTILITY ====
elseif (strpos($uriPaths, 'female-infertility') !== false) {
    $schemaType = "MedicalCondition";
    if (strpos($uriPaths, 'pcos') !== false) {
        $pageTitle = "Best PCOS Treatment for Pregnancy in Lahore, Pakistan";
        $metaDescription = "Get pregnant with PCOS. Specialized Polycystic Ovary Syndrome treatments, ovulation induction, and PCOS-friendly IVF protocols in Pakistan.";
    }
    elseif (strpos($uriPaths, 'endometriosis') !== false) {
        $pageTitle = "Endometriosis & Infertility Treatment in Lahore, Pakistan";
        $metaDescription = "Advanced Endometriosis treatment for infertility. Maximize your chances of natural pregnancy or IVF success with expert care in Lahore.";
    }
    elseif (strpos($uriPaths, 'blocked-tubes') !== false) {
        $pageTitle = "Blocked Fallopian Tubes Treatment | IVF Alternatives in Pakistan";
        $metaDescription = "Bilateral tubal blockage? Learn about your options for getting pregnant, including advanced IVF protocols for blocked fallopian tubes in Lahore.";
    }
    elseif (strpos($uriPaths, 'diminished-ovarian-reserve') !== false) {
        $pageTitle = "Low AMH & Diminished Ovarian Reserve Treatment Pakistan";
        $metaDescription = "Pregnancy with Low AMH is possible. Individualized ovarian stimulation and advanced reproductive techniques for Diminished Ovarian Reserve in Lahore.";
    }
    elseif (strpos($uriPaths, 'recurrent-pregnancy-loss') !== false) {
        $pageTitle = "Recurrent Pregnancy Loss & Miscarriage Treatment | Lahore";
        $metaDescription = "Expert care for recurrent miscarriages and repeated IVF failures in Pakistan. Advanced immunological testing and PGT-A chromosomal screening to secure your pregnancy.";
    }
    elseif (strpos($uriPaths, 'unexplained-infertility') !== false) {
        $pageTitle = "Unexplained Infertility Specialist in Lahore, Pakistan";
        $metaDescription = "Frustrated by unexplained infertility? Dr. Adnan Jabbar offers deep diagnostics and targeted ART solutions to overcome idiopathic infertility in Pakistan.";
    }
    elseif (strpos($uriPaths, 'uterine-fibroids-polyps') !== false) {
        $pageTitle = "Uterine Fibroids & Polyps Treatment for Fertility Pakistan";
        $metaDescription = "Optimize your uterus for implantation. Minimally invasive hysteroscopic removal of fibroids and polyps by leading fertility experts in Lahore.";
    }
    elseif (strpos($uriPaths, 'adenomyosis') !== false) {
        $pageTitle = "Adenomyosis Fertility Treatment & IVF | Lahore, Pakistan";
        $metaDescription = "Specialized IVF protocols downregulating severe adenomyosis. Maximize your chances of embryo implantation and a healthy pregnancy with Dr. Adnan Jabbar.";
    }
    elseif (strpos($uriPaths, 'primary-ovarian-failure') !== false) {
        $pageTitle = "Primary Ovarian Failure (POF) Treatment in Lahore, Pakistan";
        $metaDescription = "Advanced fertility treatments for Premature Ovarian Failure (POI/POF). Explore IVF protocols and stem cell ovarian rejuvenation in Pakistan.";
    }
    elseif (strpos($uriPaths, 'ovarian-tissue-preservation') !== false) {
        $pageTitle = "Ovarian Tissue Preservation & Oncofertility in Pakistan";
        $metaDescription = "Preserve your fertility before cancer treatment. Advanced ovarian tissue freezing and oncofertility services in Lahore, Pakistan.";
    }
    elseif (strpos($uriPaths, 'stemcell-ovarian-rejuvenation') !== false) {
        $pageTitle = "Stem Cell Ovarian Rejuvenation Therapy | Lahore, Pakistan";
        $metaDescription = "Revitalize failing ovaries with cutting-edge Stem Cell Ovarian Rejuvenation in Pakistan. Improve egg quality and restore natural fertility.";
    }
    else {
        $pageTitle = "Female Infertility Specialist | PCOS & IVF Expert in Lahore";
        $metaDescription = "Comprehensive female infertility treatments in Pakistan. Overcome PCOS, Endometriosis, Low AMH, and unexplainable infertility with Dr. Adnan Jabbar.";
    }
}

// ==== ART PROCEDURES ====
elseif (strpos($uriPaths, 'art-procedures') !== false) {
    $schemaType = "MedicalProcedure";
    if (strpos($uriPaths, 'ivf') !== false) {
        $pageTitle = "Best IVF Center & Treatment Cost in Lahore, Pakistan";
        $metaDescription = "Highest IVF success rates in Lahore. Learn about the In Vitro Fertilization process, affordable costs, and personalized protocols for patients across Pakistan.";
        $ogImage = "https://ivfexperts.pk/assets/images/hero_ivf_procedure.png";
    }
    elseif (strpos($uriPaths, 'icsi') !== false) {
        $pageTitle = "ICSI Treatment in Pakistan | Advanced Male Infertility Solutions";
        $metaDescription = "Intracytoplasmic Sperm Injection (ICSI) experts in Lahore. The ultimate solution for severe male infertility, low sperm motility, and prior IVF failures.";
    }
    elseif (strpos($uriPaths, 'pgt') !== false) {
        $pageTitle = "Gender Selection & PGT-A Testing in Lahore, Pakistan";
        $metaDescription = "Preimplantation Genetic Testing (PGT) for gender selection and chromosomal screening in Pakistan. Ensure a healthy baby and family balancing with 99% accuracy.";
    }
    elseif (strpos($uriPaths, 'iui') !== false) {
        $pageTitle = "IUI Cost & Success Rate in Pakistan | Intrauterine Insemination";
        $metaDescription = "Affordable IUI (Intrauterine Insemination) treatments in Lahore. Safe, effective first-line fertility treatments for couples in Pakistan.";
    }
    elseif (strpos($uriPaths, 'fertility-preservation') !== false) {
        $pageTitle = "Egg & Sperm Freezing (Fertility Preservation) Cost Lahore";
        $metaDescription = "Take control of your biological clock. Advanced ultra-fast vitrification for egg freezing, sperm banking, and oncology fertility preservation in Pakistan.";
    }
    elseif (strpos($uriPaths, 'ovarian-endometrial-prp') !== false) {
        $pageTitle = "Ovarian & Endometrial PRP Rejuvenation Treatment Pakistan";
        $metaDescription = "Boost your IVF success with Platelet-Rich Plasma (PRP) therapy in Lahore. Groundbreaking treatments for thin endometrium and dormant ovaries.";
    }
    elseif (strpos($uriPaths, 'surgical-sperm-retrieval') !== false) {
        $pageTitle = "Surgical Sperm Retrieval (PESA, TESA, Micro-TESE) Pakistan";
        $metaDescription = "Virtually painless surgical sperm retrieval procedures in Lahore. PESA, TESA, and Micro-TESE solutions for severe male infertility and zero sperm count.";
    }
    elseif (strpos($uriPaths, 'laser-assisted-hatching') !== false) {
        $pageTitle = "Laser Assisted Hatching for IVF Success | Lahore, Pakistan";
        $metaDescription = "Improve embryo implantation rates with state-of-the-art Laser-Assisted Hatching. Advanced embryology services by Dr. Adnan Jabbar.";
    }
    else {
        $pageTitle = "IVF, ICSI & Gender Selection Procedures | IVF Experts Pakistan";
        $metaDescription = "State-of-the-art ART procedures including IVF, ICSI, IUI, and Genetic Testing (PGT) in Lahore. World-class embryology laboratory.";
    }
}

// ==== STEM CELL ====
elseif (strpos($uriPaths, 'stemcell') !== false) {
    $schemaType = "MedicalProcedure";
    if (strpos($uriPaths, 'adscs') !== false) {
        $pageTitle = "Adipose-Derived Stem Cells (ADSCs) for Fertility | Pakistan";
        $metaDescription = "ADSC stem cell therapy for reproductive health. Regenerating endometrial lining and improving IVF outcomes at IVF Experts Lahore.";
    }
    elseif (strpos($uriPaths, 'mesenchymal-umbilical') !== false) {
        $pageTitle = "Mesenchymal & Umbilical Cord Stem Cells (MSCs/MHUCs) Fertility";
        $metaDescription = "Advanced Wharton's Jelly and MSC stem cell treatments for male and female infertility in collaboration with Sakina International Hospital, Lahore.";
    }
    elseif (strpos($uriPaths, 'pluripotent-stem-cells') !== false) {
        $pageTitle = "Pluripotent Stem Cells & In-Vitro Gametogenesis | Pakistan";
        $metaDescription = "The future of fertility: Pluripotent stem cells and IVG research. Discover cutting-edge reproductive science at IVF Experts Pakistan.";
    }
    elseif (strpos($uriPaths, 'multipotent-stem-cells') !== false) {
        $pageTitle = "Multipotent Stem Cells for Fertility Repair | IVF Experts";
        $metaDescription = "Targeted fertility repair using Multipotent Stem Cells. Ovarian, testicular, and endometrial tissue regeneration in Lahore, Pakistan.";
    }
    elseif (strpos($uriPaths, 'role-in-infertility') !== false) {
        $pageTitle = "The Role of Stem Cells in Treating Infertility | Lahore";
        $metaDescription = "How stem cell therapy works for infertility. Clinical pathways for POF, Asherman's Syndrome, and NOA with Dr. Adnan Jabbar in Pakistan.";
    }
    else {
        $pageTitle = "Stem Cell Research & Fertility Center | Lahore, Pakistan";
        $metaDescription = "Pakistan's leading center for Stem Cell in Reproductive Medicine. Pioneering treatments for severe infertility at Sakina International Hospital & UOL.";
    }
}

// ==== DOCTORS ====
elseif (strpos($uriPaths, 'doctors') !== false) {
    $schemaType = "MedicalOrganization";
    $pageTitle = "Our Expert Fertility Doctors & Specialists | IVF Experts Pakistan";
    $metaDescription = "Meet the top multi-disciplinary team of fertility specialists, IVF consultants, urologists, and gynecologists led by Dr. Adnan Jabbar in Lahore.";
}

// Override variables if defined globally before including seo.php
$pageTitle = isset($customPageTitle) ? $customPageTitle : $pageTitle;
$metaDescription = isset($customMetaDescription) ? $customMetaDescription : $metaDescription;

// Generate breadcrumb array
function generateBreadcrumb()
{
    $uri = trim($_SERVER['REQUEST_URI'], "/");
    if (empty($uri) || $uri == 'index.php') {
        return [
            ["name" => "Home", "url" => "https://ivfexperts.pk"]
        ];
    }

    $segments = explode("/", $uri);
    $breadcrumbs = [
        ["name" => "Home", "url" => "https://ivfexperts.pk"]
    ];

    $path = "";
    foreach ($segments as $segment) {
        $path .= "/" . $segment;
        $cleanPath = str_replace('.php', '', $path);
        $name = ucwords(str_replace(["-", ".php"], [" ", ""], $segment));
        $breadcrumbs[] = [
            "name" => $name,
            "url"  => "https://ivfexperts.pk" . $cleanPath
        ];
    }

    return $breadcrumbs;
}

$breadcrumbs = generateBreadcrumb();
?>