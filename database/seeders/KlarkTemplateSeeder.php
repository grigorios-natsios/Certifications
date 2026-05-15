<?php

namespace Database\Seeders;

use App\Models\CertificateCategory;
use Illuminate\Database\Seeder;

class KlarkTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<style>
    @font-face {
        font-family: 'Roboto';
        font-weight: 400;
        font-style: normal;
        src: url('fonts/Roboto-Regular.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Roboto';
        font-weight: 500;
        font-style: normal;
        src: url('fonts/Roboto-Medium.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Roboto';
        font-weight: 700;
        font-style: normal;
        src: url('fonts/Roboto-Bold.ttf') format('truetype');
    }

    @page { margin: 0; size: A4 portrait; }
    * { box-sizing: border-box; }
    html, body {
        margin: 0; padding: 0;
        width: 210mm; height: 297mm;
        font-family: 'Roboto', 'DejaVu Sans', sans-serif;
        color: #1e293b;
    }
    .page {
        position: relative;
        width: 210mm; height: 297mm;
        overflow: hidden;
    }
    .bg {
        position: absolute;
        top: 0; left: 0;
        width: 210mm; height: 297mm;
        z-index: 0;
    }
    .content {
        position: relative;
        z-index: 1;
        padding: 12mm 22mm 10mm;
        text-align: center;
    }

    .header { margin: 0 0 6mm; }
    .header img { width: 166mm; height: 22mm; display: block; }

    .title {
        font-size: 56px; font-weight: 700; letter-spacing: 3px;
        margin: 10mm 0 0; line-height: 1; color: #0f172a;
    }
    .title-divider {
        margin: 3mm auto 3mm;
        border-collapse: collapse;
    }
    .title-divider td {
        line-height: 0; font-size: 0; padding: 0;
    }
    .title-divider td.line {
        width: 38mm;
        border-bottom: 2px solid #c9a449;
    }
    .title-divider td.gap {
        width: 34mm;
    }
    .subtitle {
        font-size: 26px; font-weight: 500; letter-spacing: 5px;
        margin: 0 0 13mm; color: #0f172a;
    }

    .label {
        font-size: 12px; letter-spacing: 1.2px;
        margin: 0 0 3mm; color: #1e293b;
    }
    .name {
        font-size: 26px; font-weight: 700; letter-spacing: 4px;
        margin: 0 0 9mm; color: #0f172a;
    }

    .about { font-size: 12px; margin: 0 0 5mm; color: #1e293b; }
    .subject {
        font-size: 20px; font-weight: 700; line-height: 1.3;
        margin: 0 8mm 9mm; color: #0f172a;
    }

    .duration { font-size: 12px; margin: 0 0 4mm; color: #1e293b; }
    .dates {
        font-size: 21px; font-weight: 500; margin: 0 0 8mm; color: #0f172a;
    }
    .dates .dash { color: #0f172a; padding: 0 10px; font-weight: 700; }

    .legal {
        font-size: 10px; line-height: 1.5; text-align: justify;
        margin: 0 4mm 7mm; color: #475569;
    }

    .qr { margin: 0 auto 4mm; }

    .sig { margin-top: 2mm; }
    .sig img { display: block; margin: 0 auto; width: 32mm; height: 14mm; }
    .sig-line {
        width: 35mm; height: 2px;
        background: #c9a449; margin: 1mm auto 2mm;
    }
    .sig-name { font-size: 12px; font-weight: 500; margin: 0; color: #0f172a; }
    .sig-role { font-size: 11px; margin: 0; color: #475569; }

    .org-logo { margin-top: 4mm; }
    .org-logo img { width: 44mm; height: 12mm; display: inline-block; }

    .kdbm { font-size: 10px; margin: 1mm 0 0; color: #1e293b; }
</style>
</head>
<body>
<div class="page">
    <img class="bg" src="images/logos/background-cert.jpg" alt="">

    <div class="content">
        <div class="header">
            <img src="images/logos/eopp-ab-best.png" alt="">
        </div>

        <h1 class="title">ΒΕΒΑΙΩΣΗ</h1>
        <table class="title-divider"><tr>
            <td class="line">&nbsp;</td>
            <td class="gap">&nbsp;</td>
            <td class="line">&nbsp;</td>
        </tr></table>
        <h2 class="subtitle">ΠΑΡΑΚΟΛΟΥΘΗΣΗΣ</h2>

        <p class="label">ΒΕΒΑΙΩΝΕΤΑΙ ΟΤΙ Ο/Η</p>
        <p class="name">{{lastname}} {{name}}</p>

        <p class="about">παρακολούθησε επιτυχώς Πρόγραμμα Κατάρτισης με Αντικείμενο:</p>
        <p class="subject">ΧΕΙΡΙΣΤΗΣ Μ.Ε. ΟΜΑΔΑΣ Β ΕΙΔΙΚΟΤΗΤΑΣ 2<br>(&gt;10KW &amp; ΕΩΣ 2.500kg)</p>

        <p class="duration">Το Πρόγραμμα είχε διάρκεια <b>{{field:Διάρκεια (ώρες)}}</b> ώρες και υλοποιήθηκε το διάστημα:</p>
        <p class="dates">{{field:Περίοδος Έναρξης}} <span class="dash">-</span> {{field:Περίοδος Λήξης}}</p>

        <p class="legal">Σύμφωνα με το ΦΕΚ 3350/Β/12-09-2024, ο κάτοχος Βεβαίωσης Παρακολούθησης Ειδικού Προγράμματος Κατάρτισης αποκτά δικαίωμα άσκησης ελεύθερης επαγγελματικής δραστηριότητας σε Μηχανήματα Έργου Ομάδας Β Ειδικότητας 2, τα οποία κατατάσσονται στις περ. 2.7 και 2.8 των άρθρων 2 και 3 της υπό στοιχεία Οικ.1032/166/Φ.Γ.9.6.4(Η)/5.3.2013 υπουργικής απόφασης (ήτοι &gt;10 KW και μέγιστης ανυψωτικής ικανότητας έως 2.500 kgr).</p>

        <div class="qr">{{qr}}</div>

        <div class="sig">
            <img src="images/logos/sign-removebg-preview.png" alt="">
        </div>
        <div class="sig-line"></div>
        <p class="sig-name">Αναστάσιος Τσίτσης</p>
        <p class="sig-role">Διευθυντής Κατάρτισης</p>

        <div class="org-logo">
            <img src="images/logos/TSITSIS-LOGO.png" alt="">
        </div>
        <p class="kdbm">Α. Α. ΚΔΒΜ: 2101537</p>
    </div>
</div>
</body>
</html>
HTML;

        CertificateCategory::where('slug', 'klark')->update(['html_template' => $html]);

        $this->command->info('Klark template saved.');
    }
}
