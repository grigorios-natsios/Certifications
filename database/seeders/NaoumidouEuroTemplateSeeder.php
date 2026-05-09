<?php

namespace Database\Seeders;

use App\Models\CertificateCategory;
use Illuminate\Database\Seeder;

class NaoumidouEuroTemplateSeeder extends Seeder
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
        padding: 12mm 22mm 8mm;
        text-align: center;
    }

    .header { width: 100%; margin: 0 0 6mm; }
    .header td { vertical-align: middle; }
    .header td.left  { text-align: left; }
    .header td.right { text-align: right; }
    .header img.eoppep { height: 18mm; }
    .header img.abm    { height: 18mm; }

    .title {
        font-size: 56px; font-weight: 700; letter-spacing: 3px;
        margin: 6mm 0 0; line-height: 1; color: #0f172a;
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
        margin: 0 0 9mm; color: #0f172a;
    }

    .label {
        font-size: 12px; letter-spacing: 1.2px;
        margin: 0 0 3mm; color: #1e293b;
    }
    .name {
        font-size: 24px; font-weight: 700; letter-spacing: 4px;
        margin: 0 0 7mm; color: #0f172a;
    }

    .about { font-size: 12px; margin: 0 0 5mm; }
    .subject {
        font-size: 20px; font-weight: 700; line-height: 1.3;
        margin: 0 8mm 7mm; color: #0f172a;
    }

    .duration { font-size: 12px; margin: 0 0 4mm; }
    .dates {
        font-size: 21px; font-weight: 500; margin: 0 0 3mm; color: #0f172a;
    }
    .dates .dash { padding: 0 8px; font-weight: 700; }

    .eu-logos { margin: 6mm auto 6mm; }
    .eu-logos img { width: 90mm; }

    .qr { margin: 3mm auto 3mm; }

    .sig { margin-top: 1mm; }
    .sig img { display: block; margin: 0 auto; width: 30mm; height: 12mm; }
    .sig-line {
        width: 38mm; height: 2px;
        background: #c9a449; margin: 0 auto 1mm;
    }
    .sig-name { font-size: 12px; font-weight: 500; margin: 0; color: #0f172a; }
    .sig-role { font-size: 11px; margin: 0; color: #475569; }

    .org-logo { margin-top: 3mm; }
    .org-logo img { width: 50mm; }
    .kdbm { font-size: 10px; margin: 1mm 0 0; color: #1e293b; }
</style>
</head>
<body>
<div class="page">
    <img class="bg" src="images/logos/background-cert.jpg" alt="">

    <div class="content">
        <table class="header">
            <tr>
                <td class="left"><img class="eoppep" src="images/logos/eoppep.png" alt=""></td>
                <td class="right"><img class="abm" src="images/logos/abm.png" alt=""></td>
            </tr>
        </table>

        <h1 class="title">ΒΕΒΑΙΩΣΗ</h1>
        <table class="title-divider"><tr>
            <td class="line">&nbsp;</td>
            <td class="gap">&nbsp;</td>
            <td class="line">&nbsp;</td>
        </tr></table>
        <h2 class="subtitle">ΠΑΡΑΚΟΛΟΥΘΗΣΗΣ</h2>

        <p class="label">ΒΕΒΑΙΩΝΕΤΑΙ ΟΤΙ Ο/Η</p>
        <p class="name">{{full_name}}</p>

        <p class="about">παρακολούθησε επιτυχώς Πρόγραμμα Κατάρτισης με Αντικείμενο:</p>
        <p class="subject">{{field:Αντικείμενο Προγράμματος}}</p>

        <p class="duration">Το Πρόγραμμα είχε διάρκεια <b>{{field:Διάρκεια (ώρες)}}</b> ώρες και υλοποιήθηκε το διάστημα:</p>
        <p class="dates">{{field:Περίοδος Έναρξης}} <span class="dash">-</span> {{field:Περίοδος Λήξης}}</p>

        <div class="eu-logos">
            <img src="images/logos/ellada.png" alt="Ελλάδα 2.0 — Ευρωπαϊκή Ένωση NextGenerationEU">
        </div>

        <div class="qr">{{qr}}</div>

        <div class="sig">
            <img src="images/logos/sign-removebg-preview.png" alt="">
        </div>
        <div class="sig-line"></div>
        <p class="sig-name">Αναστάσιος Τσίτσης</p>
        <p class="sig-role">Διευθυντής Κατάρτισης</p>

        <div class="org-logo">
            <img src="images/logos/naoumidou.png" alt="">
        </div>
        <p class="kdbm">Α. Α. ΚΔΒΜ: 2101537</p>
    </div>
</div>
</body>
</html>
HTML;

        CertificateCategory::where('slug', 'naoumidou-euro')->update(['html_template' => $html]);

        $this->command->info('Naoumidou-Euro template saved.');
    }
}
