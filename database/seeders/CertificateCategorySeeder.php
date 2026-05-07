<?php

namespace Database\Seeders;

use App\Models\CertificateCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CertificateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $klarkTemplate = $this->klarkTemplate();

        $categories = [
            ['name' => 'tsitsis-euro',   'html_template' => null],
            ['name' => 'tsitsis',        'html_template' => null],
            ['name' => 'naoumidou-euro', 'html_template' => null],
            ['name' => 'naoumidou',      'html_template' => null],
            ['name' => 'klark',          'html_template' => $klarkTemplate],
        ];

        foreach ($categories as $cat) {
            CertificateCategory::updateOrCreate(
                ['name' => $cat['name']],
                [
                    'slug'          => Str::slug($cat['name']),
                    'html_template' => $cat['html_template'],
                ]
            );
        }
    }

    private function klarkTemplate(): string
    {
        return <<<'HTML'
<style>
@page { size: A4 portrait; margin: 0; }
body { margin: 0; padding: 0; font-family: 'DejaVu Sans', Arial, sans-serif; color: #111111; }
.cert-page { position: relative; width: 794px; height: 1123px; background: #ffffff; overflow: hidden; }
.cert-bg { position: absolute; top: 0; left: 0; width: 794px; height: 1123px; z-index: 0; }
.cert-content { position: relative; z-index: 1; padding: 60px 75px 40px 75px; text-align: center; }

.logos { display: table; width: 100%; margin-bottom: 56px; }
.logo-cell { display: table-cell; vertical-align: middle; }
.logo-cell img { height: 78px; width: auto; }
.logo-cell.left  { text-align: left; }
.logo-cell.right { text-align: right; }

.title-wrap { margin: 8px auto 0; padding: 0 80px 8px; }
.title { font-size: 64px; letter-spacing: 14px; font-weight: 700; margin: 0; line-height: 1; color: #111111; }
.title-rule { width: 360px; height: 1px; background: #5b6370; margin: 14px auto 0; }
.subtitle { font-size: 28px; letter-spacing: 10px; font-weight: 400; margin: 16px 0 0; color: #2d3748; }

.intro { font-size: 15px; margin: 64px 0 0; color: #1a1a1a; }
.client-name { font-size: 28px; font-weight: 700; letter-spacing: 2px; margin: 26px 0; color: #0f172a; }
.subject-intro { font-size: 14px; margin: 12px 0; color: #1a1a1a; }
.subject { font-size: 18px; font-weight: 700; margin: 16px 30px; color: #0f172a; line-height: 1.45; }

.duration { font-size: 14px; margin-top: 30px; color: #1a1a1a; }
.period { font-size: 20px; margin: 14px 0; letter-spacing: 1px; color: #0f172a; }

.legal { text-align: justify; font-size: 12px; line-height: 1.7; color: #2a2f3a; margin: 30px 50px 0; }

.qr-wrap { margin-top: 22px; }
.qr-wrap img { width: 110px; height: 110px; }
.qr-placeholder { display: inline-block; width: 110px; height: 110px; border: 1px solid #cbd5e1; }

.signature { margin-top: 18px; }
.sig-line { width: 240px; margin: 0 auto; border-top: 1px solid #4a5160; padding-top: 6px; }
.sig-name { font-size: 14px; font-weight: 700; margin: 0; color: #0f172a; }
.sig-role { font-size: 13px; margin: 2px 0 0; color: #2d3748; }

.bottom-logo { margin-top: 14px; }
.bottom-logo img { height: 46px; }
.kdvm { font-size: 11px; color: #4b5563; margin-top: 4px; letter-spacing: 1px; }
</style>

<div class="cert-page">
    <svg class="cert-bg" viewBox="0 0 794 1123" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <polygon points="0,0 240,0 0,330" fill="#1e3a5f" fill-opacity="0.95"/>
        <polygon points="794,0 554,0 794,330" fill="#1e3a5f" fill-opacity="0.95"/>
        <polygon points="0,1123 240,1123 0,793" fill="#1e3a5f" fill-opacity="0.95"/>
        <polygon points="794,1123 554,1123 794,793" fill="#1e3a5f" fill-opacity="0.95"/>

        <polygon points="0,0 397,210 794,0" fill="#1e3a5f" fill-opacity="0.30"/>
        <polygon points="0,1123 397,913 794,1123" fill="#1e3a5f" fill-opacity="0.30"/>

        <polygon points="0,330 200,561 0,793" fill="#1e3a5f" fill-opacity="0.18"/>
        <polygon points="794,330 594,561 794,793" fill="#1e3a5f" fill-opacity="0.18"/>

        <polygon points="397,210 200,1123 594,1123" fill="#1e3a5f" fill-opacity="0.07"/>
        <polygon points="397,210 0,1123 397,1123" fill="#3b5ea0" fill-opacity="0.05"/>
        <polygon points="397,210 794,1123 397,1123" fill="#3b5ea0" fill-opacity="0.05"/>

        <polygon points="0,0 794,1123 794,1100 0,0" fill="#1e3a5f" fill-opacity="0.05"/>
        <polygon points="794,0 0,1123 0,1100 794,0" fill="#1e3a5f" fill-opacity="0.05"/>
    </svg>

    <div class="cert-content">
        <div class="logos">
            <div class="logo-cell left">
                <img src="images/logos/eoppep.png" alt="ΕΟΠΠΕΠ"/>
            </div>
            <div class="logo-cell right">
                <img src="images/logos/abm.png" alt="Γενική Γραμματεία Δια Βίου Μάθησης"/>
            </div>
        </div>

        <div class="title-wrap">
            <h1 class="title">ΒΕΒΑΙΩΣΗ</h1>
            <div class="title-rule"></div>
            <h2 class="subtitle">ΠΑΡΑΚΟΛΟΥΘΗΣΗΣ</h2>
        </div>

        <p class="intro">ΒΕΒΑΙΩΝΕΤΑΙ ΟΤΙ Ο/Η</p>
        <p class="client-name">{{full_name}}</p>

        <p class="subject-intro">παρακολούθησε επιτυχώς Πρόγραμμα Κατάρτισης με Αντικείμενο:</p>
        <p class="subject">{{field:Αντικείμενο Προγράμματος}}</p>

        <p class="duration">Το Πρόγραμμα είχε διάρκεια {{field:Διάρκεια (ώρες)}} ώρες και υλοποιήθηκε το διάστημα:</p>
        <p class="period">{{field:Περίοδος Έναρξης}} &nbsp;-&nbsp; {{field:Περίοδος Λήξης}}</p>

        <p class="legal">
            Σύμφωνα με το ΦΕΚ 3350/Β/12-09-2024, ο κάτοχος Βεβαίωσης Παρακολούθησης Ειδικού Προγράμματος Κατάρτισης αποκτά δικαίωμα άσκησης ελεύθερης επαγγελματικής δραστηριότητας σε Μηχανήματα Έργου Ομάδας Β Ειδικότητας 2, τα οποία κατατάσσονται στις περ. 2.7 και 2.8 των άρθρων 2 και 3 της υπό στοιχεία Οικ.1032/166/Φ.Γ.9.6.4(Η)/5.3.2013 υπουργικής απόφασης (ήτοι &gt;10 KW και μέγιστης ανυψωτικής ικανότητας έως 2.500 kgr).
        </p>

        <div class="qr-wrap">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{url_slug}}" alt="QR"/>
        </div>

        <div class="signature">
            <div class="sig-line">
                <p class="sig-name">Αναστάσιος Τσίτσης</p>
                <p class="sig-role">Διευθυντής Κατάρτισης</p>
            </div>
        </div>

        <div class="bottom-logo">
            <img src="images/logos/tsitsis.png" alt="TSITSIS"/>
            <p class="kdvm">Α. Α. ΚΔΒΜ: {{field:Αριθμός ΚΔΒΜ}}</p>
        </div>
    </div>
</div>
HTML;
    }
}
