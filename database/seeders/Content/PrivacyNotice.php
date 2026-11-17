<?php

namespace Database\Seeders\Content;

/**
 * Starter privacy notice (English + Bahasa Melayu, as PDPA section 7(3) requires).
 * Written for this demo clinic. A real clinic must have it reviewed by a lawyer.
 *
 * [placeholders] are replaced on the website with values from Settings → Clinic details.
 */
class PrivacyNotice
{
    public const LAST_UPDATED = '12 November 2026';

    public static function intro(): string
    {
        return '<p><strong>Demo notice:</strong> this privacy notice belongs to a fictional clinic created as a website demo. A real clinic should have its notice reviewed by a lawyer.</p>'
            .'<p>Last updated: '.self::LAST_UPDATED.'. <a href="#bahasa-melayu">Baca dalam Bahasa Melayu</a>.</p>';
    }

    public static function english(): string
    {
        return <<<'HTML'
<p>This notice explains how [clinic-name] ("we") collects and uses your personal data through this website, in line with the Personal Data Protection Act 2010 (PDPA) as amended in 2024.</p>

<h3>1. Who we are</h3>
<p>[clinic-name], [clinic-address]. Phone: [clinic-phone]. Email: [clinic-email].</p>

<h3>2. What we collect</h3>
<ul>
<li><strong>Appointment requests:</strong> your name, mobile number, email address, the service you are interested in, your preferred date and time, whether you are a new or existing patient, and any note you choose to add.</li>
<li><strong>When you contact us</strong> by phone, WhatsApp or email: the details you give us.</li>
<li><strong>Technical data:</strong> our web server records your IP address and browser type to keep the website secure. These logs are kept for up to 14 days.</li>
<li><strong>Visitor statistics:</strong> only if you accept analytics cookies (see section 11).</li>
</ul>

<h3>3. Health information</h3>
<p>Information about your health is "sensitive personal data" under the PDPA. <strong>Please do not include medical details in the website form.</strong> Your dentist will ask about your health privately at your visit. If you choose to include health information in your note, you give us your explicit consent to use it only to arrange your appointment.</p>

<h3>4. Why we use your data</h3>
<ul>
<li>to arrange, confirm, change and remind you about appointments;</li>
<li>to reply to your questions;</li>
<li>to keep our website and systems secure; and</li>
<li>to meet our legal obligations.</li>
</ul>
<p>We do not use your data for marketing, and we never sell it.</p>

<h3>5. Do you have to give us your data?</h3>
<p>Your name, mobile number and email are needed to process an online appointment request. If you prefer not to give them, please call us instead.</p>

<h3>6. Who we share it with</h3>
<ul>
<li>Our staff, only where needed for their job (for example, front desk staff handling bookings).</li>
<li>Service providers who process data for us under contract: website hosting (Hostinger), email delivery (Resend) and, only with your consent, visitor statistics (Google Analytics).</li>
<li>Authorities, where the law requires it.</li>
</ul>

<h3>7. Transfers outside Malaysia</h3>
<p>Some of our service providers store data outside Malaysia (for example, in Singapore, the European Union or the United States). We only use providers that protect personal data to a standard substantially similar to the PDPA, as section 129 of the PDPA requires.</p>

<h3>8. How long we keep it</h3>
<ul>
<li>Appointment requests are <strong>deleted automatically [retention-months] months</strong> after the appointment date.</li>
<li>Website security logs: up to 14 days.</li>
<li>Visitor statistics (if you accept): up to 14 months.</li>
<li>Your treatment records at the clinic are kept separately, as required by health regulations.</li>
</ul>

<h3>9. How we protect it</h3>
<p>The website uses an encrypted connection (HTTPS). Staff accounts are protected with strong passwords and two-factor login, and each person can only see what their role needs. Changes to appointment requests are logged, and backups are stored securely.</p>

<h3>10. If something goes wrong</h3>
<p>If a personal data breach happens, we will notify the Personal Data Protection Commissioner and, where the breach is likely to cause you significant harm, we will tell you without unnecessary delay, as the PDPA requires.</p>

<h3>11. Cookies</h3>
<ul>
<li><strong>Essential cookies</strong> keep the website working and secure (for example, protecting forms from misuse and remembering your cookie choice). They do not track you.</li>
<li><strong>Analytics cookies</strong> (Google Analytics: <code>_ga</code>, <code>_ga_*</code>) help us understand how the website is used. They are <strong>only set if you click "Accept"</strong>, and you can change your mind at any time using the "Cookie settings" link at the bottom of every page.</li>
</ul>

<h3>12. Your rights</h3>
<p>You may ask us to:</p>
<ul>
<li>give you a copy of the personal data we hold about you;</li>
<li>correct data that is inaccurate or out of date;</li>
<li>delete your appointment request data, or stop using it;</li>
<li>withdraw your consent; and</li>
<li>send your data to you or another organisation in a commonly used format (data portability), where the PDPA allows.</li>
</ul>
<p>Contact our Data Protection Officer below. We will reply within 21 days. To protect you, we may ask you to confirm your identity first. You may also complain to the Personal Data Protection Commissioner (<a href="https://www.pdp.gov.my">www.pdp.gov.my</a>).</p>

<h3>13. Contact our Data Protection Officer</h3>
<p>[dpo-name]<br>Email: [dpo-email]<br>Phone: [dpo-phone]</p>

<h3>14. Changes to this notice</h3>
<p>We will update this page if our practices change. If there is any inconsistency between the English and Bahasa Melayu versions, the English version prevails.</p>
HTML;
    }

    public static function malay(): string
    {
        return <<<'HTML'
<p>Notis ini menerangkan cara [clinic-name] ("kami") mengumpul dan menggunakan data peribadi anda melalui laman web ini, selaras dengan Akta Perlindungan Data Peribadi 2010 (PDPA) sebagaimana dipinda pada tahun 2024.</p>

<h3>1. Siapa kami</h3>
<p>[clinic-name], [clinic-address]. Telefon: [clinic-phone]. E-mel: [clinic-email].</p>

<h3>2. Data yang kami kumpul</h3>
<ul>
<li><strong>Permohonan temujanji:</strong> nama, nombor telefon bimbit, alamat e-mel, perkhidmatan yang diminati, tarikh dan masa pilihan, sama ada anda pesakit baharu atau sedia ada, dan sebarang nota yang anda tambah.</li>
<li><strong>Apabila anda menghubungi kami</strong> melalui telefon, WhatsApp atau e-mel: maklumat yang anda berikan.</li>
<li><strong>Data teknikal:</strong> pelayan web kami merekod alamat IP dan jenis pelayar anda untuk keselamatan laman web. Rekod ini disimpan sehingga 14 hari.</li>
<li><strong>Statistik pelawat:</strong> hanya jika anda menerima kuki analitik (lihat bahagian 11).</li>
</ul>

<h3>3. Maklumat kesihatan</h3>
<p>Maklumat tentang kesihatan anda ialah "data peribadi sensitif" di bawah PDPA. <strong>Sila jangan masukkan butiran perubatan dalam borang laman web.</strong> Doktor gigi anda akan bertanya tentang kesihatan anda secara peribadi semasa lawatan. Jika anda memilih untuk memasukkan maklumat kesihatan dalam nota, anda memberikan persetujuan nyata kepada kami untuk menggunakannya bagi mengatur temujanji anda sahaja.</p>

<h3>4. Tujuan kami menggunakan data anda</h3>
<ul>
<li>untuk mengatur, mengesahkan, menukar dan mengingatkan anda tentang temujanji;</li>
<li>untuk menjawab pertanyaan anda;</li>
<li>untuk memastikan laman web dan sistem kami selamat; dan</li>
<li>untuk mematuhi kewajipan undang-undang kami.</li>
</ul>
<p>Kami tidak menggunakan data anda untuk pemasaran dan tidak sekali-kali menjualnya.</p>

<h3>5. Adakah anda wajib memberikan data?</h3>
<p>Nama, nombor telefon bimbit dan e-mel anda diperlukan untuk memproses permohonan temujanji dalam talian. Jika anda tidak mahu memberikannya, sila hubungi kami melalui telefon.</p>

<h3>6. Dengan siapa kami berkongsi data</h3>
<ul>
<li>Kakitangan kami, hanya apabila perlu untuk tugas mereka (contohnya, kakitangan kaunter yang menguruskan temujanji).</li>
<li>Pembekal perkhidmatan yang memproses data bagi pihak kami di bawah kontrak: pengehosan laman web (Hostinger), penghantaran e-mel (Resend) dan, hanya dengan persetujuan anda, statistik pelawat (Google Analytics).</li>
<li>Pihak berkuasa, apabila dikehendaki oleh undang-undang.</li>
</ul>

<h3>7. Pemindahan ke luar Malaysia</h3>
<p>Sesetengah pembekal perkhidmatan kami menyimpan data di luar Malaysia (contohnya di Singapura, Kesatuan Eropah atau Amerika Syarikat). Kami hanya menggunakan pembekal yang melindungi data peribadi pada tahap yang sebahagian besarnya serupa dengan PDPA, sebagaimana dikehendaki oleh seksyen 129 PDPA.</p>

<h3>8. Tempoh penyimpanan</h3>
<ul>
<li>Permohonan temujanji <strong>dipadam secara automatik [retention-months] bulan</strong> selepas tarikh temujanji.</li>
<li>Rekod keselamatan laman web: sehingga 14 hari.</li>
<li>Statistik pelawat (jika anda bersetuju): sehingga 14 bulan.</li>
<li>Rekod rawatan anda di klinik disimpan secara berasingan, sebagaimana dikehendaki oleh peraturan kesihatan.</li>
</ul>

<h3>9. Cara kami melindungi data</h3>
<p>Laman web ini menggunakan sambungan yang disulitkan (HTTPS). Akaun kakitangan dilindungi dengan kata laluan yang kukuh dan log masuk dua faktor, dan setiap orang hanya boleh melihat apa yang diperlukan oleh peranan mereka. Perubahan pada permohonan temujanji direkodkan, dan salinan sandaran disimpan dengan selamat.</p>

<h3>10. Jika berlaku pelanggaran data</h3>
<p>Jika berlaku pelanggaran data peribadi, kami akan memaklumkan Pesuruhjaya Perlindungan Data Peribadi dan, jika pelanggaran itu mungkin menyebabkan kemudaratan ketara kepada anda, kami akan memaklumkan anda tanpa kelewatan yang tidak wajar, sebagaimana dikehendaki oleh PDPA.</p>

<h3>11. Kuki</h3>
<ul>
<li><strong>Kuki penting</strong> memastikan laman web berfungsi dan selamat (contohnya, melindungi borang daripada penyalahgunaan dan mengingati pilihan kuki anda). Kuki ini tidak menjejaki anda.</li>
<li><strong>Kuki analitik</strong> (Google Analytics: <code>_ga</code>, <code>_ga_*</code>) membantu kami memahami cara laman web digunakan. Kuki ini <strong>hanya ditetapkan jika anda klik "Accept"</strong>, dan anda boleh menukar pilihan pada bila-bila masa melalui pautan "Cookie settings" di bahagian bawah setiap halaman.</li>
</ul>

<h3>12. Hak anda</h3>
<p>Anda boleh meminta kami untuk:</p>
<ul>
<li>memberikan salinan data peribadi anda yang kami simpan;</li>
<li>membetulkan data yang tidak tepat atau lapuk;</li>
<li>memadam data permohonan temujanji anda, atau berhenti menggunakannya;</li>
<li>menarik balik persetujuan anda; dan</li>
<li>menghantar data anda kepada anda atau organisasi lain dalam format yang biasa digunakan (kemudahalihan data), setakat yang dibenarkan oleh PDPA.</li>
</ul>
<p>Hubungi Pegawai Perlindungan Data kami di bawah. Kami akan membalas dalam tempoh 21 hari. Untuk melindungi anda, kami mungkin meminta anda mengesahkan identiti terlebih dahulu. Anda juga boleh membuat aduan kepada Pesuruhjaya Perlindungan Data Peribadi (<a href="https://www.pdp.gov.my">www.pdp.gov.my</a>).</p>

<h3>13. Hubungi Pegawai Perlindungan Data kami</h3>
<p>[dpo-name]<br>E-mel: [dpo-email]<br>Telefon: [dpo-phone]</p>

<h3>14. Perubahan pada notis ini</h3>
<p>Kami akan mengemas kini halaman ini jika amalan kami berubah. Sekiranya terdapat sebarang percanggahan antara versi Bahasa Inggeris dan Bahasa Melayu, versi Bahasa Inggeris akan diguna pakai.</p>
HTML;
    }
}
