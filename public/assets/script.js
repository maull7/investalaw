const serviceDetails = {
  "Nasihat Hukum Reksa Dana": {
    desc: "Pendampingan hukum untuk struktur produk reksa dana, kontrak investasi kolektif, peran Manajer Investasi dan Bank Kustodian, perubahan produk, serta aspek perlindungan pemegang unit.",
    outputs: ["Checklist kepatuhan reksa dana", "Review KIK dan prospektus", "Legal memo risiko produk", "Daftar dokumen untuk koordinasi pihak terkait"]
  },
  "Pendampingan Emisi & Penawaran Umum": {
    desc: "Pendampingan hukum dalam proses emisi efek, termasuk struktur transaksi, penyiapan dokumen, koordinasi legal due diligence, dan penyelarasan keterbukaan informasi.",
    outputs: ["Legal due diligence report", "Review prospektus", "Action plan kepatuhan", "Daftar isu hukum material"]
  },
  "Review Prospektus & Keterbukaan Informasi": {
    desc: "Review narasi, fakta material, risiko usaha, penggunaan dana, struktur transaksi, dan kewajiban keterbukaan informasi agar lebih jelas dan konsisten.",
    outputs: ["Catatan review prospektus", "Matriks disclosure", "Daftar gap dokumen", "Rekomendasi revisi klausul"]
  },
  "Aksi Korporasi & RUPS": {
    desc: "Pendampingan hukum untuk RUPS, perubahan anggaran dasar, rights issue, private placement, merger, akuisisi, divestasi, dan aksi korporasi lain.",
    outputs: ["Timeline aksi korporasi", "Checklist dokumen RUPS", "Review pengumuman/keterbukaan informasi", "Legal memo struktur aksi korporasi"]
  },
  "Due Diligence Investasi": {
    desc: "Legal due diligence untuk investor, perusahaan target, manajer investasi, startup, venture capital, joint venture, dan transaksi strategis.",
    outputs: ["Legal due diligence report", "Risk register", "Red flag summary", "Rekomendasi mitigasi dan kondisi pendahuluan"]
  },
  "Sengketa Pasar Modal": {
    desc: "Analisis awal sengketa pasar modal, strategi penyelesaian, pengumpulan bukti, dan pendampingan komunikasi dengan pihak terkait.",
    outputs: ["Legal position paper", "Kronologi dan matriks bukti", "Strategi penyelesaian", "Draft surat atau tanggapan awal"]
  }
};

const qs = (selector, parent = document) => parent.querySelector(selector);
const qsa = (selector, parent = document) => [...parent.querySelectorAll(selector)];

function showToast(message) {
  const toast = qs("#toast");
  toast.textContent = message;
  toast.classList.add("show");
  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(() => toast.classList.remove("show"), 2600);
}

function initNavigation() {
  const toggle = qs("#navToggle");
  const nav = qs("#mainNav");
  toggle.addEventListener("click", () => {
    const open = nav.classList.toggle("open");
    toggle.setAttribute("aria-expanded", String(open));
  });
  qsa("#mainNav a").forEach(a => a.addEventListener("click", () => nav.classList.remove("open")));
}

function initServiceFilters() {
  const tabs = qsa(".tab");
  const cards = qsa(".service-card");
  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      tabs.forEach(t => t.classList.remove("active"));
      tab.classList.add("active");
      const filter = tab.dataset.filter;
      cards.forEach(card => {
        const show = filter === "all" || card.dataset.category === filter;
        card.style.display = show ? "block" : "none";
      });
    });
  });
}

function initServiceModal() {
  const modal = qs("#serviceModal");
  const title = qs("#modalTitle");
  const desc = qs("#modalDescription");
  const outputs = qs("#modalOutputs");

  qsa(".open-service").forEach(btn => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".service-card");
      const serviceName = card.dataset.service;
      const data = serviceDetails[serviceName];

      title.textContent = serviceName;
      desc.textContent = data.desc;
      outputs.innerHTML = data.outputs.map(item => `<li>${item}</li>`).join("");
      modal.classList.add("open");
      modal.setAttribute("aria-hidden", "false");
    });
  });

  qsa("[data-close-modal]").forEach(el => {
    el.addEventListener("click", () => {
      modal.classList.remove("open");
      modal.setAttribute("aria-hidden", "true");
    });
  });

  document.addEventListener("keydown", event => {
    if (event.key === "Escape") {
      modal.classList.remove("open");
      modal.setAttribute("aria-hidden", "true");
    }
  });
}

function initLegalChecker() {
  const form = qs("#legalCheckForm");
  const box = qs("#recommendationBox");
  const text = qs("#recommendationText");
  const tags = qs("#recommendationTags");

  form.addEventListener("submit", event => {
    event.preventDefault();

    const activity = qs("#activityType").value;
    const status = qs("#companyStatus").value;
    const value = qs("#transactionValue").value;
    const output = qs("#targetOutput").value;

    let score = 0;
    if (["penawaran_umum", "aksi_korporasi", "investasi_akuisisi"].includes(activity)) score += 2;
    if (["terbuka", "mi"].includes(status)) score += 2;
    if (["high", "very_high"].includes(value)) score += 2;
    if (["legal_opinion", "pendampingan"].includes(output)) score += 1;

    let level = "Standar";
    let recommendation = "Anda dapat memulai dengan review dokumen dan checklist kepatuhan awal.";
    if (score >= 5) {
      level = "Prioritas Tinggi";
      recommendation = "Disarankan melakukan legal due diligence, legal opinion, dan pendampingan transaksi secara penuh karena terdapat indikasi kompleksitas dan risiko hukum tinggi.";
    } else if (score >= 3) {
      level = "Menengah";
      recommendation = "Disarankan melakukan legal review terstruktur, pemetaan kewajiban regulasi, dan penyusunan legal memo sebelum transaksi dilanjutkan.";
    }

    const tagList = [
      `Risiko: ${level}`,
      activity.replaceAll("_", " "),
      status.replaceAll("_", " "),
      output.replaceAll("_", " ")
    ];

    text.textContent = recommendation;
    tags.innerHTML = tagList.map(tag => `<span>${tag}</span>`).join("");
    box.hidden = false;
    showToast("Rekomendasi awal berhasil dibuat.");
  });

  qs("#copyRecommendationBtn").addEventListener("click", async () => {
    const content = `${qs("#recommendationText").textContent}\n${qsa("#recommendationTags span").map(s => s.textContent).join(" | ")}`;
    try {
      await navigator.clipboard.writeText(content);
      showToast("Rekomendasi disalin.");
    } catch {
      showToast("Browser belum mengizinkan copy otomatis.");
    }
  });
}

function initChecklistDownload() {
  qs("#downloadChecklistBtn").addEventListener("click", () => {
    const checklist = [
      "CHECKLIST AWAL INVESTASI & PASAR MODAL - INVESTALAW",
      "",
      "1. Identitas pihak dan kewenangan penandatangan",
      "2. Tujuan transaksi dan struktur investasi",
      "3. Status perusahaan, pemegang saham, dan organ perseroan",
      "4. Dokumen perizinan dan kepatuhan sektor terkait",
      "5. Dokumen keterbukaan informasi / prospektus bila relevan",
      "6. Kontrak utama, term sheet, KIK, atau perjanjian investasi",
      "7. Riwayat sengketa, jaminan, pembatasan, atau kewajiban material",
      "8. Rencana aksi korporasi, RUPS, atau persetujuan internal",
      "9. Risiko perlindungan investor dan potensi benturan kepentingan",
      "10. Target output: legal memo, legal opinion, checklist, atau pendampingan",
      "",
      "Catatan: Checklist ini bersifat awal dan perlu disesuaikan dengan transaksi."
    ].join("\n");

    const blob = new Blob([checklist], { type: "text/plain;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "checklist-awal-investasi-pasar-modal-investalaw.txt";
    a.click();
    URL.revokeObjectURL(url);
    showToast("Checklist diunduh.");
  });
}

function initDocs() {
  const selectedDocs = new Set();
  qsa("#documentList button").forEach(btn => {
    btn.addEventListener("click", () => {
      const doc = btn.dataset.doc;
      if (selectedDocs.has(doc)) {
        selectedDocs.delete(doc);
        btn.classList.remove("selected");
        btn.style.background = "";
      } else {
        selectedDocs.add(doc);
        btn.classList.add("selected");
        btn.style.background = "#fff8eb";
      }
      showToast(selectedDocs.size ? `${selectedDocs.size} dokumen dipilih.` : "Tidak ada dokumen dipilih.");
    });
  });

  qs("#showAllDocsBtn").addEventListener("click", () => {
    qsa("#documentList button").forEach(btn => {
      selectedDocs.add(btn.dataset.doc);
      btn.style.background = "#fff8eb";
    });
    showToast("Semua dokumen ditampilkan sebagai pilihan review.");
  });
}

function initAccordion() {
  qsa(".accordion-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      btn.classList.toggle("active");
      btn.nextElementSibling.classList.toggle("open");
    });
  });
}

function initContactForm() {
  const form = qs("#consultationForm");
  const note = qs("#formNote");

  form.addEventListener("submit", event => {
    // Untuk demo static hosting, cegah submit dan tampilkan pesan.
    // Jika sudah di hosting PHP, hapus event.preventDefault() agar terkirim ke contact.php.
    event.preventDefault();

    const payload = Object.fromEntries(new FormData(form).entries());
    localStorage.setItem("investalaw_latest_consultation", JSON.stringify(payload));
    note.textContent = "Permintaan konsultasi tersimpan sebagai demo. Untuk produksi, aktifkan contact.php atau sambungkan ke API/CRM.";
    showToast("Form konsultasi berhasil diproses dalam mode demo.");
    form.reset();
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initNavigation();
  initServiceFilters();
  initServiceModal();
  initLegalChecker();
  initChecklistDownload();
  initDocs();
  initAccordion();
  initContactForm();
});
