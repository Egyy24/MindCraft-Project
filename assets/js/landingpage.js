document.addEventListener("DOMContentLoaded", function () {
  // DOM Elements
  const modalMasuk = document.getElementById("modalMasuk");
  const modalDaftar = document.getElementById("modalDaftar");
  const tombolMasuk = document.getElementById("tombolMasuk");
  const tombolDaftar = document.getElementById("tombolDaftar");
  const heroDaftar = document.getElementById("heroDaftar");
  const tutupMasuk = document.getElementById("tutupMasuk");
  const tutupDaftar = document.getElementById("tutupDaftar");
  const bukaDaftar = document.getElementById("bukaDaftar");
  const bukaMasuk = document.getElementById("bukaMasuk");
  const formMasuk = document.getElementById("formMasuk");
  const formDaftar = document.getElementById("formDaftar");
  const kontenUtama = document.getElementById("kontenUtama");
  const dashboard = document.getElementById("dashboard");
  const tombolAuth = document.getElementById("tombolAuth");
  const menuPengguna = document.getElementById("menuPengguna");
  const avatarPengguna = document.getElementById("avatarPengguna");
  const avatarDashboard = document.getElementById("avatarDashboard");
  const selamatDatangPengguna = document.getElementById(
    "selamatDatangPengguna"
  );
  const peranPengguna = document.getElementById("peranPengguna");
  const kartuStatPeran = document.getElementById("kartuStatPeran");

  // Role selection
  const opsiPeran = document.querySelectorAll(".opsi-peran");
  let peranTerpilih = "mentee";

  // User data
  let penggunaSaatIni = null;

  // Modal functions
  function openModal(modal) {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }

  function closeModal(modal) {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
  }

  // Event listeners for modals
  tombolMasuk.addEventListener("click", () => openModal(modalMasuk));
  tombolDaftar.addEventListener("click", () => openModal(modalDaftar));
  heroDaftar.addEventListener("click", () => openModal(modalDaftar));

  tutupMasuk.addEventListener("click", () => closeModal(modalMasuk));
  tutupDaftar.addEventListener("click", () => closeModal(modalDaftar));

  // Switch between login and register modals
  bukaDaftar.addEventListener("click", (e) => {
    e.preventDefault();
    closeModal(modalMasuk);
    openModal(modalDaftar);
  });

  bukaMasuk.addEventListener("click", (e) => {
    e.preventDefault();
    closeModal(modalDaftar);
    openModal(modalMasuk);
  });

  // Close modal when clicking outside
  window.addEventListener("click", (e) => {
    if (e.target === modalMasuk) closeModal(modalMasuk);
    if (e.target === modalDaftar) closeModal(modalDaftar);
  });

  // Role selection function
  opsiPeran.forEach((opsi) => {
    opsi.addEventListener("click", () => {
      opsiPeran.forEach((opt) => opt.classList.remove("aktif"));
      opsi.classList.add("aktif");
      peranTerpilih = opsi.dataset.peran;
    });
  });

  // Login form handler
  formMasuk.addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = document.getElementById("emailMasuk").value;
    const sandi = document.getElementById("sandiMasuk").value;

    try {
      const response = await fetch("login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: email,
          password: sandi,
          user_type: peranTerpilih === "mentor" ? "Mentor" : "Mentee",
        }),
      });

      const data = await response.json();

      if (data.success) {
        penggunaSaatIni = {
          id: data.user.id,
          nama: data.user.username,
          email: data.user.email,
          peran: data.user.user_type.toLowerCase(),
          inisial: data.user.username.charAt(0).toUpperCase(),
        };

        perbaruiUISetelahMasuk();
        closeModal(modalMasuk);
        alert(`Berhasil masuk sebagai ${data.user.user_type}!`);
      } else {
        alert(`Gagal masuk: ${data.message}`);
      }
    } catch (error) {
      console.error("Error during login:", error);
      alert("Terjadi kesalahan saat login");
    }
  });

  // Registration form handler
  formDaftar.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nama = document.getElementById("namaDaftar").value;
    const email = document.getElementById("emailDaftar").value;
    const sandi = document.getElementById("sandiDaftar").value;
    const konfirmasiSandi = document.getElementById("konfirmasiSandi").value;

    if (sandi !== konfirmasiSandi) {
      alert("Kata sandi tidak cocok!");
      return;
    }

    try {
      const response = await fetch("register.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          username: nama,
          email: email,
          password: sandi,
          confirm_password: konfirmasiSandi,
          user_type: peranTerpilih === "mentor" ? "Mentor" : "Mentee",
          gender: "Laki-laki",
        }),
      });

      const data = await response.json();

      if (data.success) {
        // Auto login after registration
        const loginResponse = await fetch("login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            email: email,
            password: sandi,
            user_type: peranTerpilih === "mentor" ? "Mentor" : "Mentee",
          }),
        });

        const loginData = await loginResponse.json();

        if (loginData.success) {
          penggunaSaatIni = {
            id: loginData.user.id,
            nama: loginData.user.username,
            email: loginData.user.email,
            peran: loginData.user.user_type.toLowerCase(),
            inisial: loginData.user.username.charAt(0).toUpperCase(),
          };

          perbaruiUISetelahMasuk();
          closeModal(modalDaftar);
          alert(
            `Berhasil mendaftar sebagai ${loginData.user.user_type}! Anda sekarang masuk.`
          );
        } else {
          alert(
            "Registrasi berhasil tetapi gagal login otomatis. Silakan login manual."
          );
        }
      } else {
        alert(`Gagal mendaftar: ${data.message}`);
      }
    } catch (error) {
      console.error("Error during registration:", error);
      alert("Terjadi kesalahan saat registrasi");
    }
  });

  // Update UI after login/registration
  function perbaruiUISetelahMasuk() {
    tombolAuth.style.display = "none";
    menuPengguna.style.display = "block";
    avatarPengguna.textContent = penggunaSaatIni.inisial;

    kontenUtama.style.display = "none";
    dashboard.style.display = "block";

    selamatDatangPengguna.textContent = `Selamat datang kembali, ${penggunaSaatIni.nama}!`;
    peranPengguna.textContent = `Anda masuk sebagai ${
      penggunaSaatIni.peran === "mentor" ? "Mentor" : "Mentee"
    }`;
    avatarDashboard.textContent = penggunaSaatIni.inisial;

    if (penggunaSaatIni.peran === "mentor") {
      kartuStatPeran.querySelector("h3").textContent = "Mentee Aktif";
      kartuStatPeran.classList.add("mentor");
      kartuStatPeran.classList.remove("mentee");
    } else {
      kartuStatPeran.querySelector("h3").textContent = "Mentor Aktif";
      kartuStatPeran.classList.add("mentee");
      kartuStatPeran.classList.remove("mentor");
    }
  }

  // Check session on page load
  async function checkSession() {
    try {
      const response = await fetch("check_session.php");
      const data = await response.json();

      if (data.logged_in) {
        penggunaSaatIni = {
          id: data.user.id,
          nama: data.user.username,
          email: data.user.email,
          peran: data.user.user_type.toLowerCase(),
          inisial: data.user.username.charAt(0).toUpperCase(),
        };
        perbaruiUISetelahMasuk();
      } else {
        tombolAuth.style.display = "flex";
        menuPengguna.style.display = "none";
        kontenUtama.style.display = "block";
        dashboard.style.display = "none";
      }
    } catch (error) {
      console.error("Error checking session:", error);
    }
  }
  // Logout function
  async function logout() {
    try {
      const response = await fetch("logout.php");
      const data = await response.json();

      if (data.success) {
        penggunaSaatIni = null;
        tombolAuth.style.display = "flex";
        menuPengguna.style.display = "none";
        kontenUtama.style.display = "block";
        dashboard.style.display = "none";

        formMasuk.reset();
        formDaftar.reset();

        opsiPeran.forEach((opt) => opt.classList.remove("aktif"));
        document.querySelector(".opsi-peran.mentee").classList.add("aktif");
        peranTerpilih = "mentee";
      } else {
        alert("Gagal logout");
      }
    } catch (error) {
      console.error("Error during logout:", error);
    }
  }

  // Logout when clicking avatar
  avatarPengguna.addEventListener("click", function () {
    if (confirm("Apakah Anda yakin ingin logout?")) {
      logout();
    }
  });

  // Counter animation for statistics
  function animateCounters() {
    const counters = document.querySelectorAll(".item-statistik h3");
    const speed = 200;

    counters.forEach((counter) => {
      const target = +counter.getAttribute("data-target");
      const count = +counter.innerText;
      const increment = target / speed;

      if (count < target) {
        counter.innerText = Math.ceil(count + increment);
        setTimeout(animateCounters, 1);
      } else {
        counter.innerText = target;
      }
    });
  }

  // Scroll animation handler
  const scrollElements = document.querySelectorAll(".bagian, .statistik, .cta");

  function elementInView(el, dividend = 1) {
    const elementTop = el.getBoundingClientRect().top;
    return (
      elementTop <=
      (window.innerHeight || document.documentElement.clientHeight) / dividend
    );
  }

  function displayScrollElement(element) {
    element.classList.add("scrolled");

    // Animate counters when statistics section comes into view
    if (element.classList.contains("statistik")) {
      animateCounters();
    }
  }

  function handleScrollAnimation() {
    scrollElements.forEach((el) => {
      if (elementInView(el, 1.25)) {
        displayScrollElement(el);
      }
    });
  }

  // Initialize
  window.addEventListener("load", () => {
    handleScrollAnimation();
    checkSession();
  });

  window.addEventListener("scroll", () => {
    handleScrollAnimation();
  });
});
