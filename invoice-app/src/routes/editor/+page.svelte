<script>
  import { onMount, tick } from "svelte";
  import html2canvas from "html2canvas-pro";
  import { jsPDF } from "jspdf";
  import AppSidebar from "$lib/components/AppSidebar.svelte";
  import AppHeader from "$lib/components/AppHeader.svelte";
  import Combobox from "$lib/components/Combobox.svelte";

  import Invoice01Neat from "$lib/components/invoices/Invoice01Neat.svelte";
  import Invoice02Corporate from "$lib/components/invoices/Invoice02Corporate.svelte";
  import Invoice03BoldBand from "$lib/components/invoices/Invoice03BoldBand.svelte";
  import Invoice04Gradient from "$lib/components/invoices/Invoice04Gradient.svelte";
  import Invoice05DarkStudio from "$lib/components/invoices/Invoice05DarkStudio.svelte";
  import Invoice06Luxury from "$lib/components/invoices/Invoice06Luxury.svelte";
  import Invoice07Asymmetric from "$lib/components/invoices/Invoice07Asymmetric.svelte";
  import Invoice08NatureEco from "$lib/components/invoices/Invoice08NatureEco.svelte";
  import Invoice09TechStartup from "$lib/components/invoices/Invoice09TechStartup.svelte";
  import Invoice10Freelance from "$lib/components/invoices/Invoice10Freelance.svelte";
  import InvoiceBoldTypography from "$lib/components/invoices/InvoiceBoldTypography.svelte";
  import InvoiceDarkTech from "$lib/components/invoices/InvoiceDarkTech.svelte";
  import InvoiceEditorialKoran from "$lib/components/invoices/InvoiceEditorialKoran.svelte";
  import InvoiceEleganEmas from "$lib/components/invoices/InvoiceEleganEmas.svelte";
  import InvoiceGradientVibrant from "$lib/components/invoices/InvoiceGradientVibrant.svelte";
  import InvoiceKlasikFormal from "$lib/components/invoices/InvoiceKlasikFormal.svelte";
  import InvoiceKorporatBiru from "$lib/components/invoices/InvoiceKorporatBiru.svelte";
  import InvoiceModernMinimalis from "$lib/components/invoices/InvoiceModernMinimalis.svelte";
  import InvoiceNordicEarthy from "$lib/components/invoices/InvoiceNordicEarthy.svelte";
  import InvoicePastelPlayful from "$lib/components/invoices/InvoicePastelPlayful.svelte";
  import InvoiceRetroVintage from "$lib/components/invoices/InvoiceRetroVintage.svelte";
  import InvoiceTechSaaS from "$lib/components/invoices/InvoiceTechSaaS.svelte";
  import InvoiceCurvedTeal from "$lib/components/invoices/InvoiceCurvedTeal.svelte";
  import InvoicePixelReceipt from "$lib/components/invoices/InvoicePixelReceipt.svelte";
  import InvoiceLedgerKlasik from "$lib/components/invoices/InvoiceLedgerKlasik.svelte";
  import InvoiceSwissGrid from "$lib/components/invoices/InvoiceSwissGrid.svelte";
  import InvoiceMotifWarm from "$lib/components/invoices/InvoiceMotifWarm.svelte";
  import InvoiceMinimalisMono from "$lib/components/invoices/InvoiceMinimalisMono.svelte";
  import InvoiceStudioHitam from "$lib/components/invoices/InvoiceStudioHitam.svelte";
  import InvoiceBrutalistBracket from "$lib/components/invoices/InvoiceBrutalistBracket.svelte";
  import InvoiceEditorialMerah from "$lib/components/invoices/InvoiceEditorialMerah.svelte";
  import InvoiceSidebarBiru from "$lib/components/invoices/InvoiceSidebarBiru.svelte";
  import InvoiceAuroraNight from "$lib/components/invoices/InvoiceAuroraNight.svelte";
  import InvoiceCreamMonogramme from "$lib/components/invoices/InvoiceCreamMonogramme.svelte";
  import InvoiceDottedBlueWave from "$lib/components/invoices/InvoiceDottedBlueWave.svelte";
  import InvoiceIndigoSwirl from "$lib/components/invoices/InvoiceIndigoSwirl.svelte";
  import InvoiceCrimsonVAT from "$lib/components/invoices/InvoiceCrimsonVAT.svelte";
  import InvoicePillRowsIndigo from "$lib/components/invoices/InvoicePillRowsIndigo.svelte";
  import InvoiceSignatureCream from "$lib/components/invoices/InvoiceSignatureCream.svelte";
  import InvoiceLavenderSoft from "$lib/components/invoices/InvoiceLavenderSoft.svelte";
  import InvoiceCoralGradientBar from "$lib/components/invoices/InvoiceCoralGradientBar.svelte";
  import InvoiceSlateCorporate from "$lib/components/invoices/InvoiceSlateCorporate.svelte";

  import { page } from "$app/state";
  import { goto } from "$app/navigation";

  import { lang } from "$lib/lang.svelte.js";
  import { invoiceStore } from "$lib/invoiceStore.svelte.js";
  import { clientStore } from "$lib/clientStore.svelte.js";
  import { productStore } from "$lib/productStore.svelte.js";
  import { scheduledEmailStore } from "$lib/scheduledEmailStore.svelte.js";
  import { sidebar } from "$lib/sidebarStore.svelte.js";
  import { notify } from "$lib/alerts.js";
  import { buildEmailPreviewHtml } from "$lib/emailPreview.js";

  function showFailure(error, fallback) {
    return notify.error("Proses gagal", error?.message || fallback);
  }

  function validateInvoiceItems() {
    if (invoice.items.length) return true;
    notify.warning("Item invoice belum ada", "Tambahkan minimal satu item invoice sebelum melanjutkan.");
    return false;
  }

  function validateEmailSchedule() {
    if (!recipientEmail.trim()) {
      notify.warning("Email penerima belum diisi", "Masukkan alamat email penerima sebelum mengirim.");
      return false;
    }

    if (!validateInvoiceItems()) return false;

    if (autoSendEmail && !scheduleFirstSendAt) {
      notify.warning("Waktu pengiriman belum dipilih", "Pilih waktu pengiriman pertama.");
      return false;
    }

    return true;
  }

  const actionIcons = {
    save: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>`,
    download: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>`,
    mail: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>`,
    close: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 6-12 12"></path><path d="m6 6 12 12"></path></svg>`,
  };

  // =====================================================
  // API
  // =====================================================

  const API_BASE = "https://project-invoice-production-e8ba.up.railway.app/api";
  const STORAGE_BASE = "https://project-invoice-production-e8ba.up.railway.app";

  // =====================================================
  // TEMPLATE
  // =====================================================

  const templates = [
    { name: () => `1. ${lang.t("tpl1_name")}`, component: Invoice01Neat },
    { name: () => `2. ${lang.t("tpl2_name")}`, component: Invoice02Corporate },
    { name: () => `3. ${lang.t("tpl3_name")}`, component: Invoice03BoldBand },
    { name: () => `4. ${lang.t("tpl4_name")}`, component: Invoice04Gradient },
    { name: () => `5. ${lang.t("tpl5_name")}`, component: Invoice05DarkStudio },
    { name: () => `6. ${lang.t("tpl6_name")}`, component: Invoice06Luxury },
    { name: () => `7. ${lang.t("tpl7_name")}`, component: Invoice07Asymmetric },
    { name: () => `8. ${lang.t("tpl8_name")}`, component: Invoice08NatureEco },
    { name: () => `9. ${lang.t("tpl9_name")}`, component: Invoice09TechStartup },
    { name: () => `10. ${lang.t("tpl10_name")}`, component: Invoice10Freelance },
    { name: () => `11. ${lang.t("tpl11_name")}`, component: InvoiceBoldTypography },
    { name: () => `12. ${lang.t("tpl12_name")}`, component: InvoiceDarkTech },
    { name: () => `13. ${lang.t("tpl13_name")}`, component: InvoiceEditorialKoran },
    { name: () => `14. ${lang.t("tpl14_name")}`, component: InvoiceEleganEmas },
    { name: () => `15. ${lang.t("tpl15_name")}`, component: InvoiceGradientVibrant },
    { name: () => `16. ${lang.t("tpl16_name")}`, component: InvoiceKlasikFormal },
    { name: () => `17. ${lang.t("tpl17_name")}`, component: InvoiceKorporatBiru },
    { name: () => `18. ${lang.t("tpl18_name")}`, component: InvoiceModernMinimalis },
    { name: () => `19. ${lang.t("tpl19_name")}`, component: InvoiceNordicEarthy },
    { name: () => `20. ${lang.t("tpl20_name")}`, component: InvoicePastelPlayful },
    { name: () => `21. ${lang.t("tpl21_name")}`, component: InvoiceRetroVintage },
    { name: () => `22. ${lang.t("tpl22_name")}`, component: InvoiceTechSaaS },
    { name: () => `23. ${lang.t("tpl23_name")}`, component: InvoiceCurvedTeal },
    { name: () => `24. ${lang.t("tpl24_name")}`, component: InvoicePixelReceipt },
    { name: () => `25. ${lang.t("tpl25_name")}`, component: InvoiceLedgerKlasik },
    { name: () => `26. ${lang.t("tpl26_name")}`, component: InvoiceSwissGrid },
    { name: () => `27. ${lang.t("tpl27_name")}`, component: InvoiceMotifWarm },
    { name: () => `28. ${lang.t("tpl28_name")}`, component: InvoiceMinimalisMono },
    { name: () => `29. ${lang.t("tpl29_name")}`, component: InvoiceStudioHitam },
    { name: () => `30. ${lang.t("tpl30_name")}`, component: InvoiceBrutalistBracket },
    { name: () => `31. ${lang.t("tpl31_name")}`, component: InvoiceEditorialMerah },
    { name: () => `32. ${lang.t("tpl32_name")}`, component: InvoiceSidebarBiru },
    { name: () => `33. Aurora Night`, component: InvoiceAuroraNight },
    { name: () => `34. Cream Monogramme`, component: InvoiceCreamMonogramme },
    { name: () => `35. Dotted Blue Wave`, component: InvoiceDottedBlueWave },
    { name: () => `36. Indigo Swirl`, component: InvoiceIndigoSwirl },
    { name: () => `37. Crimson VAT`, component: InvoiceCrimsonVAT },
    { name: () => `38. Pill Rows Indigo`, component: InvoicePillRowsIndigo },
    { name: () => `39. Signature Cream`, component: InvoiceSignatureCream },
    { name: () => `40. Lavender Soft`, component: InvoiceLavenderSoft },
    { name: () => `41. Coral Gradient Bar`, component: InvoiceCoralGradientBar },
    { name: () => `42. Slate Corporate`, component: InvoiceSlateCorporate },
  ];

  let selected = $state(Number(page.url.searchParams.get("template")) || 0);

  // =====================================================
  // STEP (BARU) -- 1: info dasar, 2: klien + produk + preview
  // =====================================================

  let step = $state(1);
  let stepError = $state("");

  // Section mana saja yang ada di step 1 (sisanya otomatis step 2)
  const STEP1_SECTIONS = ["company", "invoice"];

  function sectionToStep(section) {
    return STEP1_SECTIONS.includes(section) ? 1 : 2;
  }

  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  async function goNext() {
    stepError = "";

    // Validasi ringan sebelum lanjut ke step 2
    if (!invoice.from.name.trim()) {
      stepError = "❌ Nama perusahaan wajib diisi dulu.";
      return;
    }

    if (!invoice.invoiceNumber.trim()) {
      stepError = "❌ Nomor invoice wajib diisi dulu.";
      return;
    }

    step = 2;
    await tick();
    scrollToTop();
  }

  async function goBack() {
    stepError = "";
    step = 1;
    await tick();
    scrollToTop();
  }

  // =====================================================
  // CAROUSEL
  // =====================================================

  let carouselEl = $state();

  function scrollCarousel(direction) {
    if (!carouselEl) return;

    const scrollAmount = carouselEl.clientWidth * 0.8;

    carouselEl.scrollBy({
      left: direction * scrollAmount,
      behavior: "smooth",
    });
  }

  // =====================================================
  // INVOICE DATA
  // =====================================================

  function todayIso() {
    const now = new Date();
    const offset = now.getTimezoneOffset();
    return new Date(now.getTime() - offset * 60000).toISOString().slice(0, 10);
  }

  function addDaysIso(dateString, days) {
    const date = new Date(`${dateString}T00:00:00`);
    date.setDate(date.getDate() + days);
    return date.toISOString().slice(0, 10);
  }

  function generateInvoiceNumber() {
    const date = todayIso().replaceAll("-", "");
    const random = Math.floor(1000 + Math.random() * 9000);
    return `INV-${date}-${random}`;
  }

  function parseMoney(value) {
    if (typeof value === "number") return Number.isFinite(value) ? value : 0;
    const normalized = String(value ?? "")
      .replace(/Rp/gi, "")
      .replace(/\s/g, "")
      .replace(/\.(?=\d{3}(?:\D|$))/g, "")
      .replace(/,/g, ".")
      .replace(/[^\d.-]/g, "");
    const parsed = Number(normalized);
    return Number.isFinite(parsed) ? parsed : 0;
  }

  const issueDate = todayIso();

  let invoice = $state({
    invoiceNumber: generateInvoiceNumber(),
    issueDate,
    dueDate: addDaysIso(issueDate, 14),
    currency: "IDR",
    logoUrl: "",

    from: {
      name: "PT Contoh Jaya",
      address: "Jl. Merdeka No.1, Surabaya",
      email: "hello@contoh.co",
      phone: "08123456789",
    },

    to: {
      name: "",
      address: "",
      email: "",
    },

    items: [],

    taxPercent: 0,

    notes: "Pembayaran via transfer BCA 1234567890 a.n PT Contoh Jaya",

    status: "unpaid",
  });

  // =====================================================
  // KALKULASI
  // =====================================================

  let subtotal = $derived(
    invoice.items.reduce((sum, item) => {
      const q = parseMoney(item.qty);
      const p = parseMoney(item.price);

      return sum + q * p;
    }, 0),
  );

  let taxPercent = $derived(Number(invoice.taxPercent) || 0);

  let taxAmount = $derived((subtotal * taxPercent) / 100);

  let total = $derived(subtotal + taxAmount);

  let calculatedInvoice = $derived({
    ...invoice,

    items: invoice.items.map((item) => {
      const qty = parseMoney(item.qty);
      const price = parseMoney(item.price);

      return {
        ...item,
        qty,
        price,
        amount: qty * price,
      };
    }),

    subtotal,
    taxPercent,
    taxAmount,
    total,
  });

  // =====================================================
  // STATE
  // =====================================================

  let logoError = $state("");
  let previewEl = $state();

  let downloading = $state(false);
  let saving = $state(false);

  // =====================================================
  // KLIEN & PRODUK
  // =====================================================

  let clients = $state([]);
  let products = $state([]);

  let selectedClientId = $state("");
  let selectedProductId = $state("");
  let clientOptions = $derived(
    clients.map((c) => ({ value: String(c.id), label: c.name, sublabel: c.email || "" })),
  );

  let productOptions = $derived(
    products.map((p) => ({ value: String(p.id), label: p.name, sublabel: `Rp${Number(p.price).toLocaleString("id-ID")}` })),
  );

  async function loadClientsAndProducts() {
    try {
      clients = await clientStore.getAll();
      products = await productStore.getAll();
    } catch (error) {
      console.error("Load clients/products error:", error);
    }
  }

  function handleSelectClient(id) {
    selectedClientId = id;

    if (!id) return;

    const client = clients.find((c) => String(c.id) === String(id));

    if (!client) return;

    invoice.to.name = client.name || "";
    invoice.to.address = client.address || "";
    invoice.to.email = client.email || "";
  }

  function addProductItem(id = selectedProductId) {
    if (!id) return;

    const product = products.find(
      (p) => String(p.id) === String(id),
    );

    if (!product) return;

    invoice.items = [
      ...invoice.items,
      {
        description: product.name,
        qty: 1,
        price: parseMoney(product.price),
      },
    ];

    selectedProductId = "";
  }

  // =====================================================
  // EMAIL STATE
  // =====================================================

  let sendingEmail = $state(false);
  let showEmailModal = $state(false);
  let recipientEmail = $state("");

  // TRUE = kirim otomatis berulang (bukan kirim sekali di masa depan)
  let autoSendEmail = $state(false);

  // Frekuensi pengiriman berulang: hourly / daily / weekly / monthly / once
  let scheduleFrequency = $state("daily");

  // Kapan pengiriman PERTAMA dimulai (datetime-local string)
  let scheduleFirstSendAt = $state("");

  let savedInvoiceId = $state(null);

  let sidebarOpen = $derived(sidebar.open);

  let activeSection = $state("");

  let highlightTimer;

  const frequencyLabel = {
    once: "Sekali saja",
    hourly: "Setiap jam",
    daily: "Setiap hari",
    weekly: "Setiap minggu",
    monthly: "Setiap bulan",
  };

  // =====================================================
  // EVENT SECTION
  // (sekarang otomatis pindah step sesuai section yang dituju)
  // =====================================================

  async function focusSection(section) {
    if (!section) return;

    // Pindah ke step yang memuat section tersebut
    step = sectionToStep(section);

    // Tunggu DOM selesai render step baru
    await tick();

    const element = document.getElementById(section);

    if (element) {
      element.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }

    activeSection = section;

    clearTimeout(highlightTimer);

    highlightTimer = setTimeout(() => {
      activeSection = "";
    }, 1500);
  }

  function handleInvoiceSection(event) {
    focusSection(event.detail);
  }

  function scrollToHash() {
    const hash = window.location.hash;

    if (!hash) return;

    const section = decodeURIComponent(hash.replace("#", ""));

    focusSection(section);
  }

  function closeSidebar() {
    sidebar.close();
  }

  // =====================================================
  // COMPANY PROFILE
  // =====================================================

  async function loadCompanyProfile() {
    try {
      const token = localStorage.getItem("auth_token");

      if (!token) {
        goto("/login");
        return;
      }

      const response = await fetch(`${API_BASE}/company-profile`, {
        method: "GET",

        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
        },
      });

      if (response.status === 401) {
        localStorage.removeItem("auth_token");
        localStorage.removeItem("user");

        goto("/login");

        return;
      }

      if (!response.ok) {
        throw new Error("Gagal mengambil profile perusahaan.");
      }

      const profile = await response.json();

      if (profile.company_name) {
        invoice.from.name = profile.company_name;
      }

      if (profile.address) {
        invoice.from.address = profile.address;
      }

      if (profile.email) {
        invoice.from.email = profile.email;
      }

      if (profile.phone) {
        invoice.from.phone = profile.phone;
      }

      if (profile.logo_path && profile.id) {
        invoice.logoUrl = `${API_BASE}/public/company-profile/${profile.id}/logo`;
      }
    } catch (error) {
      console.error("Load company profile error:", error);
    }
  }

  async function loadSavedInvoice(id) {
    const saved = (await invoiceStore.getAll()).find((item) => String(item.id) === String(id));
    if (!saved) return;

    savedInvoiceId = saved.id;
    selected = Math.max(0, Number(saved.template_id || 1) - 1);
    invoice.invoiceNumber = saved.invoice_number || invoice.invoiceNumber;
    invoice.issueDate = String(saved.issue_date || "").slice(0, 10);
    invoice.dueDate = String(saved.due_date || "").slice(0, 10);
    invoice.currency = saved.currency || "IDR";
    invoice.logoUrl = saved.logo_url || invoice.logoUrl;
    invoice.from = { name: saved.from_name || "", address: saved.from_address || "", email: saved.from_email || "", phone: saved.from_phone || "" };
    invoice.to = { name: saved.to_name || "", address: saved.to_address || "", email: saved.to_email || "" };
    invoice.taxPercent = Number(saved.tax_percent) || 0;
    invoice.notes = saved.notes || "";
    invoice.status = saved.status || "unpaid";
    invoice.items = (saved.items || []).map((item) => ({ description: item.description || "", qty: item.qty || 1, price: item.price || 0 }));
  }

  // =====================================================
  // ON MOUNT
  // =====================================================

  onMount(async () => {
    lang.init();

    window.addEventListener("invoice-section", handleInvoiceSection);

    await loadCompanyProfile();

    const savedId = page.url.searchParams.get("invoice");
    if (savedId) await loadSavedInvoice(savedId);

    await loadClientsAndProducts();

    setTimeout(() => {
      scrollToHash();
    }, 100);

    return () => {
      window.removeEventListener("invoice-section", handleInvoiceSection);

      clearTimeout(highlightTimer);
    };
  });

  // =====================================================
  // LOGO
  // =====================================================

  function handleLogoUpload(e) {
    const file = e.target.files?.[0];

    if (!file) return;

    logoError = "";

    if (!file.type.startsWith("image/")) {
      logoError = lang.t("logo_file_error");

      return;
    }

    if (file.size > 2 * 1024 * 1024) {
      logoError = lang.t("logo_size_error");

      return;
    }

    const reader = new FileReader();

    reader.onload = (event) => {
      invoice.logoUrl = event.target.result;
    };

    reader.readAsDataURL(file);
  }

  function removeLogo() {
    invoice.logoUrl = "";
  }

  // =====================================================
  // ITEM
  // =====================================================

  function addItem() {
    invoice.items = [
      ...invoice.items,
      {
        description: "",
        qty: 1,
        price: 0,
      },
    ];
  }

  function removeItem(index) {
    invoice.items = invoice.items.filter((_, i) => i !== index);
  }

  // =====================================================
  // PDF (dipakai hanya untuk download & kirim LANGSUNG.
  // Untuk kirim OTOMATIS BERULANG, PDF dibuat oleh server
  // sendiri lewat Browsershot -- browser tidak perlu bikin PDF.)
  // =====================================================

  async function generatePDF() {
    if (!previewEl) {
      throw new Error("Preview invoice tidak ditemukan.");
    }

    // PDF must always be light regardless of app dark mode
    previewEl.classList.add("pdf-export");
    let canvas;
    try {
      canvas = await html2canvas(previewEl, {
        scale: 2,
        useCORS: true,
        allowTaint: false,
        backgroundColor: "#ffffff",
      });
    } finally {
      previewEl.classList.remove("pdf-export");
    }

    const imgData = canvas.toDataURL("image/jpeg", 0.98);

    const pdf = new jsPDF("p", "mm", "a4");

    const pageWidth = pdf.internal.pageSize.getWidth();

    const pageHeight = pdf.internal.pageSize.getHeight();

    const imgHeight = (canvas.height * pageWidth) / canvas.width;

    if (imgHeight <= pageHeight) {
      pdf.addImage(imgData, "JPEG", 0, 0, pageWidth, imgHeight);
    } else {
      let heightLeft = imgHeight;

      let position = 0;

      pdf.addImage(imgData, "JPEG", 0, position, pageWidth, imgHeight);

      heightLeft -= pageHeight;

      while (heightLeft > 0) {
        position -= pageHeight;

        pdf.addPage();

        pdf.addImage(imgData, "JPEG", 0, position, pageWidth, imgHeight);

        heightLeft -= pageHeight;
      }
    }

    return pdf;
  }

  // =====================================================
  // DOWNLOAD PDF
  // =====================================================

  async function downloadPDF() {
    downloading = true;

    try {
      const pdf = await generatePDF();

      pdf.save(`${invoice.invoiceNumber || "invoice"}.pdf`);
    } catch (error) {
      console.error("Generate PDF error:", error);

      await showFailure(error, "Gagal membuat PDF.");
    } finally {
      downloading = false;
    }
  }

  // =====================================================
  // SAVE
  // =====================================================

  async function saveInvoice() {
    if (!validateInvoiceItems()) return;

    saving = true;

    try {
      const record = savedInvoiceId
        ? await invoiceStore.update(savedInvoiceId, selected + 1, calculatedInvoice)
        : await invoiceStore.save(selected + 1, calculatedInvoice);

      savedInvoiceId = record.id;

      if (record.invoiceNumber) {
        invoice.invoiceNumber = record.invoiceNumber;
      }

      await notify.success("Invoice disimpan", "Data invoice sudah tersimpan.");
    } catch (e) {
      console.error("Save invoice error:", e);
      await showFailure(e, "Gagal menyimpan invoice.");
    } finally {
      saving = false;
    }
  }

  // =====================================================
  // EMAIL MODAL
  // =====================================================

  function openEmailModal() {
    recipientEmail = invoice.to.email || "";

    autoSendEmail = false;
    scheduleFrequency = "daily";

    // Default: mulai 1 jam dari sekarang
    const d = new Date(Date.now() + 60 * 60 * 1000);
    scheduleFirstSendAt = d.toISOString().slice(0, 16);

    showEmailModal = true;
  }

  function closeEmailModal() {
    if (sendingEmail) return;

    showEmailModal = false;
  }

  // =====================================================
  // SEND / BUAT JADWAL BERULANG
  // =====================================================

  async function sendEmail() {
    if (!validateEmailSchedule()) return;

    sendingEmail = true;

    try {
      const token = localStorage.getItem("auth_token");

      if (!token) {
        throw new Error("Kamu harus login terlebih dahulu.");
      }

      // SIMPAN INVOICE JIKA BELUM ADA ID
      if (!savedInvoiceId) {
        const record = await invoiceStore.save(selected + 1, calculatedInvoice);

        savedInvoiceId = record.id;

        if (record.invoiceNumber) {
          invoice.invoiceNumber = record.invoiceNumber;
        }
      }

      // KIRIM OTOMATIS BERULANG (diproses server, bukan browser)
      if (autoSendEmail) {
        await scheduledEmailStore.create({
          invoice_id: savedInvoiceId,
          recipient_email: recipientEmail.trim(),
          frequency: scheduleFrequency,
          next_send_at: new Date(scheduleFirstSendAt).toISOString(),
        });

        showEmailModal = false;
        await notify.success(
          "Invoice dijadwalkan",
          scheduleFrequency === "once"
            ? "Invoice akan dikirim sesuai waktu yang dipilih."
            : `Invoice akan dikirim ${frequencyLabel[scheduleFrequency].toLowerCase()} mulai waktu yang dipilih.`,
        );

        return;
      }

      // KIRIM LANGSUNG SEKARANG (gambar invoice dibuat di server dari print page)
      await invoiceStore.sendEmail(
        savedInvoiceId,
        recipientEmail,
      );


      showEmailModal = false;
      await notify.success("Invoice dikirim", "Email invoice sudah dikirim ke penerima.");
    } catch (error) {
      console.error("Send email error:", error);

      await showFailure(error, "Gagal mengirim atau menjadwalkan email.");
    } finally {
      sendingEmail = false;
    }
  }

</script>

<div
  class="min-h-screen bg-white dark:bg-black text-slate-900 dark:text-white transition-colors"
>
  <AppSidebar {sidebarOpen} onClose={closeSidebar} />

  <main
    class="min-h-screen transition-all duration-300 {sidebarOpen
      ? 'lg:ml-64'
      : 'ml-0'}"
  >
    <AppHeader title={lang.t("et_title")} />

    <!-- CONTENT -->

    <div class="pt-24 sm:pt-28 pb-6 sm:pb-8 px-4">
      <h1 class="text-2xl font-bold text-center mb-6">
        {lang.t("et_title")}
      </h1>

      <!-- ================================================= -->
      <!-- STEPPER (indikator langkah) -->
      <!-- ================================================= -->

      <div class="max-w-md mx-auto mb-8 flex items-center gap-3">
        <button
          type="button"
          onclick={goBack}
          class="flex items-center gap-2 text-sm font-semibold {step === 1
            ? ''
            : 'opacity-60 hover:opacity-100'}"
        >
          <span
            class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {step ===
            1
              ? 'text-black'
              : 'border border-slate-300 dark:border-white/20'}"
            style={step === 1 ? "background:#8CFF3D;" : ""}
          >
            1
          </span>
          Info Dasar
        </button>

        <div
          class="flex-1 h-0.5 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden"
        >
          <div
            class="h-full transition-all duration-300"
            style="background:#8CFF3D; width:{step === 2 ? '100%' : '0%'};"
          ></div>
        </div>

        <div
          class="flex items-center gap-2 text-sm font-semibold {step === 2
            ? ''
            : 'opacity-40'}"
        >
          <span
            class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {step ===
            2
              ? 'text-black'
              : 'border border-slate-300 dark:border-white/20'}"
            style={step === 2 ? "background:#8CFF3D;" : ""}
          >
            2
          </span>
          Klien, Produk &amp; Preview
        </div>
      </div>

      {#if step === 1}
        <!-- ================================================= -->
        <!-- STEP 1: INFO DASAR (logo, perusahaan, info invoice) -->
        <!-- ================================================= -->

        <div
          class="max-w-2xl mx-auto bg-slate-50 dark:bg-[#111] rounded-2xl border border-slate-200 dark:border-white/10 p-4 sm:p-6 space-y-6"
        >
          <!-- COMPANY + LOGO -->

          <div
            id="company"
            class:section-highlight={activeSection === "company"}
            class="scroll-mt-6 rounded-xl p-2 -m-2 transition-all duration-500"
          >
            <div>
              <h2 class="font-semibold mb-3">
                {lang.t("logo_company")}
              </h2>

              <div class="flex items-center gap-4">
                {#if invoice.logoUrl}
                  <img
                    src={invoice.logoUrl}
                    alt={lang.t("logo_company")}
                    class="w-16 h-16 object-contain rounded-lg border border-slate-200 dark:border-white/10 bg-white p-1"
                  />
                {:else}
                  <div
                    class="w-16 h-16 rounded-lg border-2 border-dashed border-slate-300 dark:border-white/20 flex items-center justify-center opacity-50 text-xs text-center"
                  >
                    {lang.t("logo_empty")}
                  </div>
                {/if}

                <div class="flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    onchange={handleLogoUpload}
                    class="w-full text-xs opacity-80 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:font-semibold file:text-black file:bg-[#8CFF3D] text-current"
                  />

                  {#if invoice.logoUrl}
                    <button
                      onclick={removeLogo}
                      class="text-xs text-red-500 mt-1 hover:underline"
                    >
                      {lang.t("logo_remove")}
                    </button>
                  {/if}

                  {#if logoError}
                    <p class="text-xs text-red-500 mt-1">
                      {logoError}
                    </p>
                  {/if}
                </div>
              </div>
            </div>

            <div class="mt-5">
              <h2 class="font-semibold mb-3">
                {lang.t("et_from")}
              </h2>

              <div class="space-y-2">
                <input
                  placeholder={lang.t("et_company_name")}
                  bind:value={invoice.from.name}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                />

                <input
                  placeholder={lang.t("et_address")}
                  bind:value={invoice.from.address}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                />

                <div class="grid grid-cols-2 gap-2">
                  <input
                    placeholder={lang.t("et_email")}
                    bind:value={invoice.from.email}
                    class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                  />

                  <input
                    placeholder={lang.t("et_phone")}
                    bind:value={invoice.from.phone}
                    class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- INVOICE INFO -->

          <div
            id="invoice"
            class:section-highlight={activeSection === "invoice"}
            class="scroll-mt-6 rounded-xl p-2 -m-2 transition-all duration-500"
          >
            <h2 class="font-semibold mb-3">
              {lang.t("et_info")}
            </h2>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-xs opacity-60">
                  {lang.t("et_invoice_number")}
                </label>

                <input
                  bind:value={invoice.invoiceNumber}
                  class="w-full border border-lime-300 dark:border-lime-400/40 bg-lime-50/60 dark:bg-lime-400/10 rounded-lg px-3 py-1.5 text-sm mt-1 font-semibold tracking-wide text-slate-800 dark:text-white shadow-sm focus:border-lime-500 focus:ring-2 focus:ring-lime-400/30 outline-none"
                />
                <p class="text-[10px] text-lime-700 dark:text-lime-300 mt-1">Otomatis dibuat unik. Bisa kamu ubah manual.</p>
              </div>

              <div>
                <label class="text-xs opacity-60">
                  {lang.t("et_status")}
                </label>

                <select
                  bind:value={invoice.status}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm mt-1"
                >
                  <option value="unpaid">{lang.t("et_status_unpaid")}</option>
                  <option value="paid">{lang.t("et_status_paid")}</option>
                  <option value="overdue">{lang.t("et_status_overdue")}</option>
                </select>
              </div>

              <div>
                <label class="text-xs opacity-60">
                  {lang.t("et_issue_date")}
                </label>

                <input
                  type="date"
                  bind:value={invoice.issueDate}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm mt-1"
                />
              </div>

              <div>
                <label class="text-xs opacity-60">
                  {lang.t("et_due_date")}
                </label>

                <input
                  type="date"
                  bind:value={invoice.dueDate}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm mt-1"
                />
              </div>
            </div>
          </div>

          {#if stepError}
            <p class="text-sm text-red-500 text-center">{stepError}</p>
          {/if}

          <button
            type="button"
            onclick={goNext}
            class="w-full px-4 py-2.5 rounded-full font-semibold text-black"
            style="background:#8CFF3D"
          >
            Lanjut ke Preview &amp; Produk →
          </button>
        </div>
      {:else}
        <!-- ================================================= -->
        <!-- STEP 2: KLIEN + PRODUK + PREVIEW -->
        <!-- ================================================= -->

        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-[minmax(0,0.82fr)_minmax(0,1.18fr)] gap-4 sm:gap-6">
          <!-- FORM STEP 2 -->

          <div
            class="bg-slate-50 dark:bg-[#111] rounded-2xl border border-slate-200 dark:border-white/10 p-4 sm:p-6 space-y-5 h-fit"
          >
            <button
              type="button"
              onclick={goBack}
              class="text-sm opacity-70 hover:opacity-100 transition"
            >
              ← Kembali ke Info Dasar
            </button>

            <!-- CUSTOMER -->

            <div
              id="customer"
              class:section-highlight={activeSection === "customer"}
              class="scroll-mt-6 rounded-xl p-2 -m-2 transition-all duration-500"
            >
              <h2 class="font-semibold mb-3">
                {lang.t("et_to")}
              </h2>

              {#if clients.length > 0}
                <div class="mb-2">
                  <label class="text-xs opacity-60">
                    Pilih dari daftar klien (opsional)
                  </label>

                  <div class="mt-1">
                    <Combobox options={clientOptions} bind:value={selectedClientId} onselect={handleSelectClient} placeholder="-- Pilih klien --" searchPlaceholder="Cari klien..." emptyText="Klien tidak ditemukan" />
                  </div>
                </div>
              {:else}
                <p class="text-xs opacity-50 mb-2">
                  Belum ada klien tersimpan.
                  <a href="/clients" class="underline" style="color:#8CFF3D">
                    Tambah klien
                  </a>
                  supaya bisa dipilih langsung di sini.
                </p>
              {/if}

              <div class="space-y-2">
                <input
                  placeholder={lang.t("et_client_name")}
                  bind:value={invoice.to.name}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                />

                <input
                  placeholder={lang.t("et_address")}
                  bind:value={invoice.to.address}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                />

                <input
                  type="email"
                  placeholder={lang.t("et_email")}
                  bind:value={invoice.to.email}
                  class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                />
              </div>
            </div>

            <!-- ITEMS -->

            <div
              id="items"
              class:section-highlight={activeSection === "items"}
              class="scroll-mt-6 rounded-xl p-2 -m-2 transition-all duration-500"
            >
              <div class="mb-3">
                <h2 class="font-semibold">
                  {lang.t("et_items")}
                </h2>
                <p class="mt-1 text-xs opacity-60">
                  Pilih produk tersimpan atau buat item manual.
                </p>
              </div>

              {#if products.length > 0}
                <div class="mb-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white/60 dark:bg-white/[0.03] p-3">
                  <label class="mb-2 block text-xs font-medium opacity-70">
                    Tambahkan produk tersimpan
                  </label>
                  <Combobox options={productOptions} bind:value={selectedProductId} onselect={addProductItem} placeholder="Cari lalu pilih produk..." searchPlaceholder="Cari produk..." emptyText="Produk tidak ditemukan" />
                  <p class="mt-2 text-xs opacity-50">Produk langsung masuk ke daftar item setelah dipilih.</p>
                </div>
              {:else}
                <p class="text-xs opacity-50 mb-3">
                  Belum ada produk tersimpan.
                  <a href="/products" class="underline" style="color:#8CFF3D">
                    Tambah produk
                  </a>
                  supaya bisa dipilih langsung di sini.
                </p>
              {/if}

              <button
                onclick={addItem}
                class="mb-3 text-xs px-3 py-1.5 font-semibold text-black rounded-full"
                style="background:#8CFF3D"
              >
                + Buat item manual
              </button>

              <div class="space-y-2">
                {#each invoice.items as item, i}
                  <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <input
                      placeholder={lang.t("et_description")}
                      bind:value={item.description}
                      class="flex-1 border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm"
                    />

                    <input
                      type="number"
                      min="1"
                      placeholder={lang.t("et_qty")}
                      bind:value={item.qty}
                      class="w-16 border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-2 py-1.5 text-sm"
                    />

                    <input
                      type="number"
                      min="0"
                      placeholder={lang.t("et_price")}
                      bind:value={item.price}
                      class="w-28 border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-2 py-1.5 text-sm"
                    />

                    <button
                      onclick={() => removeItem(i)}
                      aria-label="Hapus item invoice"
                      class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-500/10"
                    >
                      {@html actionIcons.close}
                    </button>
                  </div>
                {/each}
              </div>
            </div>

            <!-- TAX -->

            <div
              id="tax"
              class:section-highlight={activeSection === "tax"}
              class="scroll-mt-6 rounded-xl p-2 -m-2 transition-all duration-500"
            >
              <label class="text-xs opacity-60">
                {lang.t("et_tax")}
              </label>

              <input
                type="number"
                min="0"
                max="100"
                bind:value={invoice.taxPercent}
                class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm mt-1"
              />
            </div>

            <!-- NOTES -->

            <div
              id="notes"
              class:section-highlight={activeSection === "notes"}
              class="scroll-mt-6 rounded-xl p-2 -m-2 transition-all duration-500"
            >
              <label class="text-xs opacity-60">
                {lang.t("et_notes")}
              </label>

              <textarea
                bind:value={invoice.notes}
                class="w-full border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-1.5 text-sm mt-1"
                rows="2"
              ></textarea>
            </div>
          </div>

          <!-- PREVIEW -->

          <div class="lg:sticky lg:top-6 h-fit">
            <div class="flex gap-2 mb-3">
              <button
                onclick={saveInvoice}
                disabled={saving || sendingEmail}
                class="flex-1 inline-flex items-center justify-center gap-2 text-black px-4 py-2 rounded-full font-semibold disabled:opacity-50"
                style="background:#8CFF3D"
              >
                <span class="shrink-0">{@html actionIcons.save}</span>
                <span>{saving ? lang.t("saving_invoice") : lang.t("save_invoice")}</span>
              </button>

              <button
                onclick={downloadPDF}
                disabled={downloading || sendingEmail}
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-semibold border-2 disabled:opacity-50"
                style="border-color:#8CFF3D; color:#8CFF3D;"
              >
                <span class="shrink-0">{@html actionIcons.download}</span>
                <span>{downloading ? lang.t("generating_pdf") : lang.t("download_pdf")}</span>
              </button>
            </div>

            <div class="mb-3">
              <button
                onclick={openEmailModal}
                disabled={saving || downloading || sendingEmail}
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-semibold border-2 disabled:opacity-50 transition"
                style="border-color:#8CFF3D; color:#8CFF3D;"
              >
                <span class="shrink-0">{@html actionIcons.mail}</span>
                <span>Kirim Email</span>
              </button>
            </div>


            <div bind:this={previewEl} class="rounded-2xl overflow-hidden bg-white dark:bg-slate-900 border border-transparent dark:border-white/10">
              <svelte:component
                this={templates[selected].component}
                invoice={calculatedInvoice}
              />
            </div>
          </div>
        </div>

        <!-- ================================================= -->
        <!-- PILIH TEMPLATE (dipindah ke bawah editor) -->
        <!-- ================================================= -->

        <div class="max-w-6xl mx-auto mt-12">
          <h2 class="text-lg font-semibold text-center mb-1">
            Ganti Template Invoice
          </h2>

          <p class="text-xs opacity-60 text-center mb-4">
            Geser untuk melihat template lain, klik untuk menerapkan ke preview.
          </p>

          <div class="relative">
            <button
              type="button"
              onclick={() => scrollCarousel(-1)}
              aria-label="Sebelumnya"
              class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 w-9 h-9 items-center justify-center rounded-full bg-white dark:bg-[#161616] border border-slate-200 dark:border-white/10 shadow-md hover:scale-105 transition"
            >
              ‹
            </button>

            <div
              bind:this={carouselEl}
              class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory px-2 py-2 no-scrollbar"
            >
              {#each templates as t, i}
                <button
                  type="button"
                  onclick={() => (selected = i)}
                  class="snap-start shrink-0 w-40 rounded-xl border-2 overflow-hidden transition text-left {selected ===
                  i
                    ? ''
                    : 'border-slate-200 dark:border-white/10 opacity-70 hover:opacity-100'}"
                  style={selected === i ? "border-color:#8CFF3D;" : ""}
                >
                  <div class="relative w-40 h-52 bg-white dark:bg-slate-900 overflow-hidden">
                    <div
                      class="absolute top-0 left-0 origin-top-left pointer-events-none"
                      style="width:800px; transform: scale(0.2);"
                    >
                      <svelte:component this={t.component} invoice={calculatedInvoice} />
                    </div>
                  </div>

                  <div
                    class="text-[11px] font-medium text-center py-1.5 border-t border-slate-200 dark:border-white/10 truncate px-1"
                    style={selected === i ? "background:#8CFF3D; color:#000;" : ""}
                  >
                    {t.name()}
                  </div>
                </button>
              {/each}
            </div>

            <button
              type="button"
              onclick={() => scrollCarousel(1)}
              aria-label="Selanjutnya"
              class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 w-9 h-9 items-center justify-center rounded-full bg-white dark:bg-[#161616] border border-slate-200 dark:border-white/10 shadow-md hover:scale-105 transition"
            >
              ›
            </button>
          </div>
        </div>
      {/if}
    </div>

    <!-- ================================================= -->
    <!-- EMAIL MODAL -->
    <!-- ================================================= -->

    {#if showEmailModal}
      <div
        class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4"
        onclick={(e) => {
          if (e.target === e.currentTarget && !sendingEmail) {
            closeEmailModal();
          }
        }}
      >
        <div
          class="w-full max-w-md bg-white dark:bg-[#111] text-slate-900 dark:text-white rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-white/10 max-h-[90vh] overflow-y-auto"
        >
          <div class="flex items-center justify-between mb-2">
            <h2 class="flex items-center gap-2 text-xl font-bold">
              <span class="shrink-0">{@html actionIcons.mail}</span>
              <span>Kirim Invoice</span>
            </h2>

            <button
              onclick={closeEmailModal}
              disabled={sendingEmail}
              aria-label="Tutup dialog kirim invoice"
              class="flex h-8 w-8 items-center justify-center rounded-lg opacity-60 hover:bg-slate-100 hover:opacity-100 dark:hover:bg-white/10 disabled:opacity-30"
            >
              {@html actionIcons.close}
            </button>
          </div>

          <p class="text-sm opacity-60 mb-5">
            Masukkan email pelanggan untuk mengirim invoice.
          </p>

          <label class="text-sm font-medium" for="recipient-email">
            Email penerima
          </label>

          <input
            id="recipient-email"
            type="email"
            bind:value={recipientEmail}
            placeholder="contoh@email.com"
            class="w-full mt-2 border border-slate-300 dark:border-white/10 bg-white dark:bg-[#161616] rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#8CFF3D]"
            disabled={sendingEmail}
          />

          <!-- KIRIM OTOMATIS BERULANG -->

          <div
            class="mt-5 p-4 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#161616]"
          >
            <label class="flex items-start gap-3 cursor-pointer">
              <input
                type="checkbox"
                bind:checked={autoSendEmail}
                disabled={sendingEmail}
                class="mt-1 w-4 h-4 accent-[#8CFF3D]"
              />

              <div class="flex-1">
                <div class="text-sm font-semibold">
                  Kirim otomatis berulang
                </div>

                <p class="text-xs opacity-60 mt-1 leading-relaxed">
                  Invoice akan dikirim otomatis oleh server sesuai frekuensi
                  yang dipilih, terus-menerus sampai kamu batalkan lewat
                  halaman "Jadwal Kirim".
                </p>
              </div>
            </label>

            {#if autoSendEmail}
              <div class="mt-4 pt-4 border-t border-slate-200 dark:border-white/10 space-y-3">
                <div>
                  <label class="text-xs opacity-60" for="schedule-frequency">
                    Frekuensi pengiriman
                  </label>

                  <select
                    id="schedule-frequency"
                    bind:value={scheduleFrequency}
                    disabled={sendingEmail}
                    class="w-full mt-1 border border-slate-300 dark:border-white/10 bg-white dark:bg-[#111] rounded-lg px-3 py-2 text-sm"
                  >
                    <option value="once">Sekali saja (satu waktu tertentu)</option>
                    <option value="hourly">Setiap jam</option>
                    <option value="daily">Setiap hari</option>
                    <option value="weekly">Setiap minggu</option>
                    <option value="monthly">Setiap bulan</option>
                  </select>
                </div>

                <div>
                  <label class="text-xs opacity-60" for="schedule-first-send">
                    Kirim pertama pada
                  </label>

                  <input
                    id="schedule-first-send"
                    type="datetime-local"
                    bind:value={scheduleFirstSendAt}
                    disabled={sendingEmail}
                    class="w-full mt-1 border border-slate-300 dark:border-white/10 bg-white dark:bg-[#111] rounded-lg px-3 py-2 text-sm"
                  />
                </div>

                {#if scheduleFrequency !== "once"}
                  <p class="text-xs opacity-50 leading-relaxed">
                    Contoh: kalau pilih <strong>Setiap hari</strong> dan mulai
                    besok jam 9 pagi, invoice akan terkirim tiap hari jam 9
                    pagi terus-menerus sampai kamu batalkan.
                  </p>
                {:else}
                  <p class="text-xs opacity-50 leading-relaxed">
                    Invoice akan dikirim tepat satu kali pada waktu yang
                    dipilih, lalu jadwal otomatis selesai.
                  </p>
                {/if}
              </div>
            {:else}
              <div class="mt-3 pt-3 border-t border-slate-200 dark:border-white/10">
                <p class="flex items-center gap-2 text-xs opacity-60 leading-relaxed">
                  <span class="shrink-0">{@html actionIcons.mail}</span>
                  <span>Invoice akan langsung dikirim sekarang ke:</span>
                </p>

                <p class="text-xs font-semibold mt-1">
                  {recipientEmail || "email penerima"}
                </p>
              </div>
            {/if}
          </div>


          <div class="flex gap-2 mt-6">
            <button
              onclick={closeEmailModal}
              disabled={sendingEmail}
              class="flex-1 px-4 py-2 rounded-full border border-slate-300 dark:border-white/20 disabled:opacity-50"
            >
              Batal
            </button>

            <button
              onclick={sendEmail}
              disabled={sendingEmail || !recipientEmail.trim()}
              class="flex-1 px-4 py-2 rounded-full font-semibold text-black disabled:opacity-50"
              style="background:#8CFF3D"
            >
              {sendingEmail
                ? autoSendEmail
                  ? "Menyimpan jadwal..."
                  : "Mengirim..."
                : autoSendEmail
                  ? "Buat Jadwal"
                  : "Kirim Invoice"}
            </button>
          </div>
        </div>
      </div>
    {/if}
  </main>
</div>

<style>
  :global(html) {
    scroll-behavior: smooth;
  }

  .section-highlight {
    background: rgba(140, 255, 61, 0.08);

    box-shadow:
      0 0 0 2px rgba(140, 255, 61, 0.45),
      0 0 25px rgba(140, 255, 61, 0.12);
  }

  .no-scrollbar::-webkit-scrollbar {
    display: none;
  }

  .no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
</style>