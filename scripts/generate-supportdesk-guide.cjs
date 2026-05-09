const fs = require("fs");
const path = require("path");
const PDFDocument = require("pdfkit");

const root = path.resolve(__dirname, "..");
const outputPath = path.join(root, "public", "docs", "SupportDesk-Pro-User-Guide.pdf");
const doc = new PDFDocument({
    size: "A4",
    margin: 46,
    bufferPages: true,
    info: {
        Title: "Panduan Penggunaan SupportDesk Pro",
        Author: "SupportDesk Pro",
        Subject: "User Guide",
        Keywords: "SupportDesk Pro, Help Center, Ticketing, User Guide",
    },
});

fs.mkdirSync(path.dirname(outputPath), { recursive: true });
doc.pipe(fs.createWriteStream(outputPath));

const page = {
    width: 595.28,
    height: 841.89,
    margin: 46,
};

const colors = {
    navy: "#17324D",
    blue: "#2F6F9F",
    cyan: "#DFF1FA",
    ink: "#1F2933",
    muted: "#667085",
    border: "#D9DEE5",
    pale: "#F5F7FA",
    white: "#FFFFFF",
    green: "#227A43",
    amber: "#946200",
};

const sections = [
    {
        title: "Login & Dashboard",
        page: 3,
        icon: "01",
        lead: "Dashboard adalah halaman awal setelah pengguna berhasil masuk. Halaman ini menampilkan ringkasan aktivitas ticket dan akses cepat ke modul utama.",
        steps: [
            "Buka alamat aplikasi SupportDesk Pro melalui browser yang direkomendasikan perusahaan.",
            "Masukkan username atau email dan password yang telah diberikan administrator.",
            "Pilih tombol Login, lalu pastikan nama pengguna muncul pada area kanan atas.",
            "Gunakan ringkasan dashboard untuk memantau jumlah ticket open, assigned, overdue, resolved, closed, dan critical.",
        ],
        notes: [
            "Jaga kerahasiaan akun dan hindari menyimpan password pada perangkat publik.",
            "Hubungi administrator apabila akun terkunci atau role akses belum sesuai.",
        ],
        screenshot: "Placeholder screenshot: Halaman login dan dashboard utama.",
    },
    {
        title: "Membuat Ticket",
        page: 4,
        icon: "02",
        lead: "Ticket digunakan untuk mencatat permintaan bantuan, kendala operasional, atau kebutuhan tindak lanjut yang perlu diproses oleh tim terkait.",
        steps: [
            "Masuk ke menu Tickets pada sidebar.",
            "Pilih tombol Create Ticket.",
            "Isi subject secara singkat dan jelas.",
            "Pilih category, priority, dan jabatan apabila tersedia.",
            "Tulis description dengan kronologi, dampak, dan informasi pendukung.",
            "Tambahkan attachment apabila diperlukan, kemudian pilih Save.",
        ],
        notes: [
            "Gunakan satu ticket untuk satu isu agar proses tracking lebih mudah.",
            "Lampirkan bukti seperti screenshot, dokumen, atau file pendukung agar proses analisis lebih cepat.",
        ],
        screenshot: "Placeholder screenshot: Form pembuatan ticket.",
    },
    {
        title: "Melihat Status Ticket",
        page: 5,
        icon: "03",
        lead: "Status ticket membantu pengguna memahami posisi penanganan saat ini, mulai dari open sampai closed.",
        steps: [
            "Buka menu Tickets.",
            "Gunakan tab atau filter untuk melihat ticket berdasarkan scope yang tersedia.",
            "Gunakan kolom status untuk melihat posisi ticket.",
            "Klik nomor ticket untuk membuka detail dan riwayat aktivitas.",
        ],
        notes: [
            "Status OPEN berarti ticket baru dibuat dan menunggu tindak lanjut.",
            "Status ASSIGNED berarti ticket sudah dialokasikan ke petugas.",
            "Status RESOLVED berarti solusi sudah diberikan dan menunggu konfirmasi.",
            "Status CLOSED berarti ticket telah selesai.",
        ],
        screenshot: "Placeholder screenshot: Tabel ticket dan badge status.",
    },
    {
        title: "Comment & Attachment",
        page: 6,
        icon: "04",
        lead: "Komentar digunakan untuk komunikasi dan dokumentasi progress pada ticket. Attachment membantu melengkapi informasi teknis atau bukti pendukung.",
        steps: [
            "Buka detail ticket.",
            "Tulis komentar pada area Comment.",
            "Pilih file attachment apabila diperlukan.",
            "Tekan Send untuk menyimpan komentar.",
            "Komentar dan lampiran akan muncul pada timeline percakapan ticket.",
        ],
        notes: [
            "Gunakan bahasa yang jelas, profesional, dan relevan dengan ticket.",
            "Pastikan file yang dilampirkan tidak mengandung data sensitif yang tidak diperlukan.",
        ],
        screenshot: "Placeholder screenshot: Area komentar dan attachment.",
    },
    {
        title: "Assignment Ticket",
        page: 7,
        icon: "05",
        lead: "Assignment digunakan untuk menunjuk petugas atau tim yang bertanggung jawab menangani ticket.",
        steps: [
            "Buka detail ticket yang perlu dialokasikan.",
            "Pada panel Actions, pilih pengguna pada field Assign To.",
            "Isi assignment note apabila perlu memberikan konteks tambahan.",
            "Pilih tombol Assign.",
            "Sistem akan mencatat riwayat assignment pada timeline activity.",
        ],
        notes: [
            "Fitur assignment mengikuti permission dan role akses yang diberikan administrator.",
            "Ticket yang sudah assigned akan lebih mudah dipantau berdasarkan penanggung jawab.",
        ],
        screenshot: "Placeholder screenshot: Panel assignment ticket.",
    },
    {
        title: "Change Status Ticket",
        page: 8,
        icon: "06",
        lead: "Perubahan status mencerminkan progress penanganan ticket dan harus dilakukan sesuai kondisi aktual.",
        steps: [
            "Buka detail ticket.",
            "Pada panel Actions, pilih status baru pada field Change Status.",
            "Tambahkan note untuk menjelaskan alasan perubahan status.",
            "Pilih tombol Update Status.",
            "Perubahan status tersimpan pada status history dan timeline activity.",
        ],
        notes: [
            "Gunakan status RESOLVED ketika solusi sudah diberikan.",
            "Gunakan status CLOSED setelah ticket dikonfirmasi selesai.",
            "Catatan status membantu audit dan penelusuran proses.",
        ],
        screenshot: "Placeholder screenshot: Panel change status ticket.",
    },
    {
        title: "Notification",
        page: 9,
        icon: "07",
        lead: "Notification membantu pengguna mengetahui update penting, seperti ticket assigned, komentar baru, atau perubahan status.",
        steps: [
            "Perhatikan ikon bell pada topbar aplikasi.",
            "Klik ikon bell untuk melihat daftar notifikasi terbaru.",
            "Pilih notifikasi untuk membuka halaman terkait.",
            "Gunakan Mark All as Read apabila ingin menandai seluruh notifikasi sebagai sudah dibaca.",
        ],
        notes: [
            "Periksa notifikasi secara berkala agar tidak melewatkan ticket prioritas.",
            "Notifikasi menampilkan ringkasan sehingga pengguna dapat langsung memahami konteks update.",
        ],
        screenshot: "Placeholder screenshot: Dropdown notification pada topbar.",
    },
    {
        title: "Settings",
        page: 10,
        icon: "08",
        lead: "Settings digunakan untuk mengatur konfigurasi aplikasi sesuai kebijakan internal dan kebutuhan operasional.",
        steps: [
            "Buka menu Settings pada sidebar.",
            "Pilih konfigurasi yang ingin diperbarui.",
            "Ubah value sesuai kebutuhan dan tipe data yang tersedia.",
            "Simpan perubahan dan lakukan pengecekan pada fitur terkait.",
        ],
        notes: [
            "Akses settings sebaiknya diberikan hanya kepada administrator atau role yang berwenang.",
            "Catat perubahan konfigurasi penting agar mudah diaudit.",
        ],
        screenshot: "Placeholder screenshot: Halaman settings aplikasi.",
    },
    {
        title: "FAQ Singkat",
        page: 11,
        icon: "09",
        lead: "Bagian ini merangkum pertanyaan umum yang sering muncul saat menggunakan SupportDesk Pro.",
        faq: [
            ["Saya tidak bisa login. Apa yang harus dilakukan?", "Pastikan username, email, dan password benar. Jika masih gagal, hubungi administrator untuk pengecekan status akun."],
            ["Apakah ticket bisa diberi attachment?", "Bisa. Pengguna dapat melampirkan file pendukung ketika membuat ticket atau menambahkan komentar."],
            ["Bagaimana mengetahui ticket saya sudah ditangani?", "Buka detail ticket dan lihat status, assignee, komentar, serta timeline activity."],
            ["Siapa yang dapat melakukan assignment dan change status?", "Fitur tersebut mengikuti permission yang ditetapkan administrator."],
            ["Apakah notifikasi wajib dibaca?", "Disarankan dibaca secara berkala karena berisi update penting terkait ticket."],
        ],
        screenshot: "Placeholder screenshot: FAQ dan Help Center internal.",
    },
];

function rect(x, y, w, h, fill, stroke = null) {
    doc.save();
    if (fill) doc.fillColor(fill).rect(x, y, w, h).fill();
    if (stroke) doc.strokeColor(stroke).rect(x, y, w, h).stroke();
    doc.restore();
}

function text(value, x, y, options = {}) {
    doc.fillColor(options.color || colors.ink)
        .font(options.font || "Helvetica")
        .fontSize(options.size || 10)
        .text(value, x, y, {
            width: options.width,
            align: options.align || "left",
            lineGap: options.lineGap || 2,
            continued: options.continued || false,
        });
}

function header(title) {
    rect(0, 0, page.width, 58, colors.white);
    doc.moveTo(page.margin, 58).lineTo(page.width - page.margin, 58).strokeColor(colors.border).stroke();
    text("SupportDesk Pro", page.margin, 22, { size: 11, font: "Helvetica-Bold", color: colors.blue });
    text(title, page.width - 260, 22, { size: 9, color: colors.muted, width: 214, align: "right" });
}

function footer(pageNumber, total) {
    const y = page.height - 76;
    doc.moveTo(page.margin, y).lineTo(page.width - page.margin, y).strokeColor(colors.border).stroke();
    text("Panduan Penggunaan SupportDesk Pro", page.margin, y + 12, { size: 8, color: colors.muted });
    text(`Halaman ${pageNumber} dari ${total}`, page.width - 160, y + 12, { size: 8, color: colors.muted, width: 114, align: "right" });
}

function pill(label, x, y, fill = colors.cyan, color = colors.blue) {
    doc.roundedRect(x, y, doc.widthOfString(label) + 20, 22, 11).fillColor(fill).fill();
    text(label, x + 10, y + 6, { size: 8, font: "Helvetica-Bold", color });
}

function screenshotBox(label, x, y, w, h) {
    doc.save();
    doc.roundedRect(x, y, w, h, 8).fillColor(colors.pale).fill();
    doc.roundedRect(x, y, w, h, 8).dash(5, { space: 4 }).strokeColor("#B8C2CC").stroke();
    doc.undash();
    doc.circle(x + w / 2, y + h / 2 - 16, 18).strokeColor("#9AA7B2").stroke();
    text("Screenshot Placeholder", x, y + h / 2 + 10, { size: 12, font: "Helvetica-Bold", color: colors.muted, width: w, align: "center" });
    text(label, x + 28, y + h / 2 + 30, { size: 9, color: colors.muted, width: w - 56, align: "center" });
    doc.restore();
}

function bulletList(items, x, y, w, title) {
    text(title, x, y, { size: 12, font: "Helvetica-Bold", color: colors.navy });
    y += 22;
    items.forEach((item) => {
        doc.circle(x + 4, y + 5, 2.3).fillColor(colors.blue).fill();
        text(item, x + 14, y, { size: 10, color: colors.ink, width: w - 14, lineGap: 3 });
        y += doc.heightOfString(item, { width: w - 14, lineGap: 3 }) + 9;
    });
    return y;
}

function cover() {
    rect(0, 0, page.width, page.height, "#F3F7FA");
    rect(0, 0, page.width, 260, colors.navy);
    rect(0, 260, page.width, 12, colors.blue);
    doc.circle(494, 76, 86).fillColor("#244C70").fill();
    doc.circle(532, 156, 62).fillColor("#2F6F9F").fill();
    pill("USER GUIDE", page.margin, 74, colors.cyan, colors.blue);
    text("SupportDesk Pro", page.margin, 120, { size: 30, font: "Helvetica-Bold", color: colors.white, width: 420 });
    text("Panduan Penggunaan Aplikasi", page.margin, 160, { size: 18, color: colors.white, width: 420 });
    text("Dokumen resmi untuk pengguna dan client dalam memahami proses ticketing, notifikasi, dan konfigurasi dasar aplikasi.", page.margin, 198, { size: 11, color: "#D7E7F2", width: 430, lineGap: 4 });
    rect(page.margin, 326, page.width - page.margin * 2, 310, colors.white, colors.border);
    text("Ruang Lingkup Panduan", page.margin + 26, 356, { size: 16, font: "Helvetica-Bold", color: colors.navy });
    const items = ["Login & Dashboard", "Membuat dan memantau ticket", "Komentar, attachment, assignment, dan perubahan status", "Notification, Settings, serta FAQ singkat"];
    bulletList(items, page.margin + 28, 396, 450, "Materi Utama");
    screenshotBox("Area ini dapat diganti dengan screenshot dashboard perusahaan.", page.margin + 28, 512, page.width - page.margin * 2 - 56, 90);
    text(`Versi Dokumen: 1.0\nTanggal Terbit: ${new Date().toLocaleDateString("id-ID", { day: "2-digit", month: "long", year: "numeric" })}`, page.margin, 690, { size: 10, color: colors.muted, width: 250, lineGap: 4 });
}

function tableOfContents() {
    header("Daftar Isi");
    text("Daftar Isi", page.margin, 92, { size: 24, font: "Helvetica-Bold", color: colors.navy });
    text("Gunakan daftar isi berikut untuk menemukan topik penggunaan SupportDesk Pro dengan cepat.", page.margin, 128, { size: 11, color: colors.muted, width: 440, lineGap: 3 });
    let y = 182;
    sections.forEach((section) => {
        doc.roundedRect(page.margin, y - 8, page.width - page.margin * 2, 34, 5).fillColor(section.page % 2 === 0 ? "#FBFCFD" : colors.white).fill();
        text(section.icon, page.margin + 12, y, { size: 9, font: "Helvetica-Bold", color: colors.blue, width: 28 });
        text(section.title, page.margin + 48, y, { size: 11, font: "Helvetica-Bold", color: colors.ink, width: 360 });
        doc.moveTo(page.width - 128, y + 8).lineTo(page.width - 74, y + 8).dash(1, { space: 3 }).strokeColor("#B8C2CC").stroke();
        doc.undash();
        text(String(section.page), page.width - 66, y, { size: 11, font: "Helvetica-Bold", color: colors.navy, width: 20, align: "right" });
        y += 42;
    });
}

function sectionPage(section) {
    header(section.title);
    pill(section.icon, page.margin, 88, colors.cyan, colors.blue);
    text(section.title, page.margin, 122, { size: 23, font: "Helvetica-Bold", color: colors.navy, width: 460 });
    text(section.lead, page.margin, 158, { size: 11, color: colors.muted, width: 480, lineGap: 4 });

    screenshotBox(section.screenshot, page.margin, 218, page.width - page.margin * 2, 190);

    if (section.faq) {
        let y = 444;
        section.faq.forEach(([question, answer], index) => {
            doc.roundedRect(page.margin, y, page.width - page.margin * 2, 56, 6).fillColor(index % 2 === 0 ? "#FBFCFD" : colors.white).fill();
            text(question, page.margin + 16, y + 10, { size: 10.5, font: "Helvetica-Bold", color: colors.navy, width: 460 });
            text(answer, page.margin + 16, y + 28, { size: 9.5, color: colors.muted, width: 470 });
            y += 66;
        });
        return;
    }

    const left = page.margin;
    const right = page.margin + 258;
    const yStart = 446;
    bulletList(section.steps, left, yStart, 230, "Langkah Penggunaan");

    doc.roundedRect(right, yStart - 8, 245, 210, 6).fillColor("#FBFCFD").strokeColor(colors.border).stroke();
    text("Catatan Penting", right + 16, yStart + 12, { size: 12, font: "Helvetica-Bold", color: colors.navy });
    let y = yStart + 42;
    section.notes.forEach((note) => {
        doc.circle(right + 20, y + 5, 2.3).fillColor(colors.green).fill();
        text(note, right + 31, y, { size: 9.8, color: colors.ink, width: 190, lineGap: 3 });
        y += doc.heightOfString(note, { width: 190, lineGap: 3 }) + 13;
    });
}

cover();
doc.addPage();
tableOfContents();
sections.forEach((section) => {
    doc.addPage();
    sectionPage(section);
});

const range = doc.bufferedPageRange();
const totalPages = range.count;
for (let i = 0; i < totalPages; i += 1) {
    doc.switchToPage(i);
    footer(i + 1, totalPages);
}

doc.end();
console.log(outputPath);
