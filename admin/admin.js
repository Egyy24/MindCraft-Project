let currentUser = null;
let currentContent = null;
let userDistributionChart,
  userGrowthChart,
  contentStatusChart,
  contentCategoryChart;

document.addEventListener("DOMContentLoaded", function () {
  initializeCharts();
  loadDashboardData();
  loadUsers();
  loadContent();
  setInterval(loadDashboardData, 30000); // Refresh setiap 30 detik
});

function initializeCharts() {
  // Distribusi user (Doughnut)
  userDistributionChart = new Chart(
    document.getElementById("userDistributionChart").getContext("2d"),
    {
      type: "doughnut",
      data: {
        labels: ["Mentee", "Mentor"],
        datasets: [
          {
            data: [0, 0],
            backgroundColor: ["#4cc9f0", "#f72585"],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" },
          tooltip: {
            callbacks: {
              label: function (context) {
                const label = context.label || "";
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage =
                  total > 0 ? Math.round((value / total) * 100) : 0;
                return `${label}: ${value} (${percentage}%)`;
              },
            },
          },
        },
        cutout: "70%",
      },
    }
  );

  // Pertumbuhan user(Linechart)
  userGrowthChart = new Chart(
    document.getElementById("userGrowthChart").getContext("2d"),
    {
      type: "line",
      data: {
        labels: [],
        datasets: [
          {
            label: "Mentee",
            data: [],
            borderColor: "#4cc9f0",
            backgroundColor: "rgba(76, 201, 240, 0.1)",
            tension: 0.3,
            fill: true,
          },
          {
            label: "Mentor",
            data: [],
            borderColor: "#f72585",
            backgroundColor: "rgba(247, 37, 133, 0.1)",
            tension: 0.3,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: "bottom" } },
        scales: { y: { beginAtZero: true } },
      },
    }
  );

  // Status konten(Piechart)
  contentStatusChart = new Chart(
    document.getElementById("contentStatusChart").getContext("2d"),
    {
      type: "pie",
      data: {
        labels: ["Published", "Draft", "Archived"],
        datasets: [
          {
            data: [0, 0, 0],
            backgroundColor: ["#4cc9f0", "#f8961e", "#f72585"],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "bottom" },
          tooltip: {
            callbacks: {
              label: function (context) {
                const label = context.label || "";
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage =
                  total > 0 ? Math.round((value / total) * 100) : 0;
                return `${label}: ${value} (${percentage}%)`;
              },
            },
          },
        },
      },
    }
  );

  // Kategori konten (Barchart)
  contentCategoryChart = new Chart(
    document.getElementById("contentCategoryChart").getContext("2d"),
    {
      type: "bar",
      data: {
        labels: [],
        datasets: [
          {
            label: "Jumlah Konten",
            data: [],
            backgroundColor: "rgba(67, 97, 238, 0.7)",
            borderColor: "#4361ee",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } },
      },
    }
  );
}

// Navigasi functions
function showDashboard() {
  document.getElementById("dashboard-content").style.display = "block";
  document.getElementById("users-content").style.display = "none";
  document.getElementById("content-content").style.display = "none";
  updateActiveMenu("dashboard");
  loadDashboardData();
}

function showUsers() {
  document.getElementById("dashboard-content").style.display = "none";
  document.getElementById("users-content").style.display = "block";
  document.getElementById("content-content").style.display = "none";
  updateActiveMenu("users");
  loadUsers();
}

function showContent() {
  document.getElementById("dashboard-content").style.display = "none";
  document.getElementById("users-content").style.display = "none";
  document.getElementById("content-content").style.display = "block";
  updateActiveMenu("content");
  loadContent();
}

function updateActiveMenu(activeItem) {
  const menuItems = document.querySelectorAll(".sidebar-menu a");
  menuItems.forEach((item) => item.classList.remove("active"));

  if (activeItem === "dashboard") menuItems[0].classList.add("active");
  else if (activeItem === "users") menuItems[1].classList.add("active");
  else if (activeItem === "content") menuItems[2].classList.add("active");
}

// Load data dashboard
async function loadDashboardData() {
  try {
    document.querySelectorAll(".value").forEach((el) => {
      if (!el.querySelector(".loading")) {
        el.innerHTML = `<span class="loading"></span>`;
      }
    });

    const [statsResponse, chartDataResponse] = await Promise.all([
      fetch("api.php?entity=stats"),
      fetch("api.php?entity=chart-data"),
    ]);

    if (!statsResponse.ok || !chartDataResponse.ok) {
      throw new Error("Failed to fetch data");
    }

    const stats = await statsResponse.json();
    const chartData = await chartDataResponse.json();

    if (stats.success) {
      document.getElementById("total-users").textContent =
        stats.data.total_users;
      document.getElementById("total-mentees").textContent =
        stats.data.total_mentees;
      document.getElementById("total-mentors").textContent =
        stats.data.total_mentors;
      document.getElementById("total-contents").textContent =
        stats.data.total_contents;

      const lastUpdated = new Date(stats.timestamp * 1000);
      document.getElementById(
        "last-updated"
      ).textContent = `Terakhir diperbarui: ${lastUpdated.toLocaleTimeString()}`;
    }

    if (chartData.success) {
      updateCharts(chartData.data);
    }
  } catch (error) {
    console.error("Error loading dashboard data:", error);
    showErrorNotification("Gagal memuat data dashboard");
  }
}

function updateCharts(data) {
  // Chart distribusi user
  userDistributionChart.data.datasets[0].data = [
    data.user_distribution.find((d) => d.user_type === "Mentee")?.count || 0,
    data.user_distribution.find((d) => d.user_type === "Mentor")?.count || 0,
  ];
  userDistributionChart.update();

  // Chart pertumbuhan user
  const growthData = data.user_growth;
  userGrowthChart.data.labels = growthData.map((d) => d.month);
  userGrowthChart.data.datasets[0].data = growthData.map((d) => d.mentees);
  userGrowthChart.data.datasets[1].data = growthData.map((d) => d.mentors);
  userGrowthChart.update();

  // Chart status kategori
  contentStatusChart.data.datasets[0].data = [
    data.content_status.find((d) => d.status === "Published")?.count || 0,
    data.content_status.find((d) => d.status === "Draft")?.count || 0,
    data.content_status.find((d) => d.status === "Archived")?.count || 0,
  ];
  contentStatusChart.update();

  // Chart kategori konten
  const categories = data.content_category;
  contentCategoryChart.data.labels = categories.map((d) => d.category);
  contentCategoryChart.data.datasets[0].data = categories.map((d) => d.count);
  contentCategoryChart.data.datasets[0].backgroundColor = categories.map(
    (_, i) => {
      const hue = (i * 360) / categories.length;
      return `hsl(${hue}, 70%, 60%)`;
    }
  );
  contentCategoryChart.update();
}

// Load data user
async function loadUsers() {
  try {
    showLoading();
    const response = await fetch("api.php?entity=users");

    if (!response.ok) {
      throw new Error("Failed to fetch users");
    }

    const data = await response.json();

    if (data.success) {
      displayUsers(data.data);
    } else {
      displayUsers([]);
    }
  } catch (error) {
    console.error("Error loading users:", error);
    showErrorNotification("Gagal memuat data user");
    displayUsers([]);
  } finally {
    hideLoading();
  }
}

// Menampilkan data user di tabel
function displayUsers(users) {
  const tableBody = document.querySelector("#users-table tbody");
  tableBody.innerHTML = "";

  if (users && users.length > 0) {
    users.forEach((user, index) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${index + 1}</td>
        <td>${user.username}</td>
        <td>${user.email}</td>
        <td>${user.user_type}</td>
        <td>${user.gender}</td>
        <td>${new Date(user.created_at).toLocaleDateString()}</td>
        <td class="action-buttons">
            <button class="btn btn-warning btn-sm" onclick="editUser(${
              user.id
            })">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="deleteUser(${
              user.id
            })">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  } else {
    const row = document.createElement("tr");
    row.innerHTML = `<td colspan="7" class="text-center">Tidak ada data user yang tersedia</td>`;
    tableBody.appendChild(row);
  }
}

// Load data konten
async function loadContent() {
  try {
    showLoading();
    const response = await fetch("api.php?entity=content");

    if (!response.ok) {
      throw new Error("Failed to fetch content");
    }

    const data = await response.json();

    if (data.success) {
      displayContent(data.data);
    } else {
      displayContent([]);
    }
  } catch (error) {
    console.error("Error loading content:", error);
    showErrorNotification("Gagal memuat data konten");
    displayContent([]);
  } finally {
    hideLoading();
  }
}

// Menampilkan konten di tabel
function displayContent(content) {
  const tableBody = document.querySelector("#content-table tbody");
  tableBody.innerHTML = "";

  if (content && content.length > 0) {
    content.forEach((item, index) => {
      let statusClass = "";
      if (item.status === "Published") statusClass = "badge-success";
      else if (item.status === "Draft") statusClass = "badge-warning";
      else if (item.status === "Archived") statusClass = "badge-danger";

      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${index + 1}</td>
        <td><img src="${
          item.thumbnail || "https://via.placeholder.com/50"
        }" alt="Thumbnail" style="width: 50px; height: auto;"></td>
        <td>${item.title}</td>
        <td>${item.category}</td>
        <td><span class="badge ${statusClass}">${item.status}</span></td>
        <td>${new Date(item.created_at).toLocaleDateString()}</td>
        <td class="action-buttons">
            <button class="btn btn-warning btn-sm" onclick="editContent(${
              item.id
            })">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="deleteContent(${
              item.id
            })">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  } else {
    const row = document.createElement("tr");
    row.innerHTML = `<td colspan="7" class="text-center">Tidak ada data konten yang tersedia</td>`;
    tableBody.appendChild(row);
  }
}

// User CRUD
function openUserModal(user = null) {
  const modal = document.getElementById("user-modal");
  const title = document.getElementById("user-modal-title");
  const form = document.getElementById("user-form");
  const passwordFields = document.getElementById("password-fields");

  if (user) {
    title.textContent = "Edit User";
    document.getElementById("user-id").value = user.id;
    document.getElementById("username").value = user.username;
    document.getElementById("email").value = user.email;
    document.getElementById("user-type").value = user.user_type;
    document.getElementById("gender").value = user.gender;

    // Kosongkan password dan sembunyikan field konfirmasi
    document.getElementById("password").value = "";
    document.getElementById("confirm-password").value = "";
    passwordFields.style.display = "none";
  } else {
    title.textContent = "Tambah User";
    form.reset();
    document.getElementById("user-id").value = "";
    passwordFields.style.display = "block";
  }

  modal.style.display = "flex";
}

async function saveUser() {
  const id = document.getElementById("user-id").value;
  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm-password").value;
  const userType = document.getElementById("user-type").value;
  const gender = document.getElementById("gender").value;

  // Validasi lebih ketat
  if (!username || username.length < 3) {
    showErrorNotification("Username harus diisi dan minimal 3 karakter!");
    return;
  }

  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showErrorNotification("Email harus valid!");
    return;
  }

  if (!userType || !gender) {
    showErrorNotification("Role dan gender harus diisi!");
    return;
  }

  // Validasi password hanya untuk user baru atau jika password diisi
  if ((!id || password) && password !== confirmPassword) {
    showErrorNotification("Password dan konfirmasi password tidak cocok!");
    return;
  }

  if (!id && !password) {
    showErrorNotification("Password harus diisi untuk user baru!");
    return;
  }

  if (password && password.length < 6) {
    showErrorNotification("Password minimal 6 karakter!");
    return;
  }

  const userData = {
    username,
    email,
    user_type: userType,
    gender,
  };

  if (password) {
    userData.password = password;
  }

  try {
    showLoading();
    const method = id ? "PUT" : "POST";
    const url = `api.php?entity=users${id ? `&id=${id}` : ""}`;

    const response = await fetch(url, {
      method,
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(userData),
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
      throw new Error(data.message || "Gagal menyimpan user");
    }

    showSuccessNotification(
      id ? "User berhasil diperbarui" : "User berhasil ditambahkan"
    );
    closeModal("user-modal");
    await loadUsers();
    await loadDashboardData();
  } catch (error) {
    console.error("Error saving user:", error);
    showErrorNotification(error.message || "Gagal menyimpan user");
  } finally {
    hideLoading();
  }
}

async function editUser(id) {
  try {
    showLoading();
    const response = await fetch(`api.php?entity=users&id=${id}`);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success) {
      openUserModal(data.data);
    } else {
      throw new Error(data.message || "Failed to fetch user data");
    }
  } catch (error) {
    console.error("Error fetching user:", error);
    showErrorNotification("Gagal memuat data user: " + error.message);
  } finally {
    hideLoading();
  }
}

async function deleteUser(id) {
  if (!confirm("Apakah Anda yakin ingin menghapus user ini?")) {
    return;
  }

  try {
    showLoading();
    const response = await fetch(`api.php?entity=users&id=${id}`, {
      method: "DELETE",
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
      throw new Error(data.message || "Failed to delete user");
    }

    showSuccessNotification("User berhasil dihapus");
    await loadUsers();
    await loadDashboardData();
  } catch (error) {
    console.error("Error deleting user:", error);
    showErrorNotification("Gagal menghapus user");
  } finally {
    hideLoading();
  }
}

// Konten CRUD
function openContentModal(content = null) {
  const modal = document.getElementById("content-modal");
  const title = document.getElementById("content-modal-title");
  const form = document.getElementById("content-form");

  if (content) {
    title.textContent = "Edit Konten";
    document.getElementById("content-id").value = content.id;
    document.getElementById("thumbnail").value = content.thumbnail;
    document.getElementById("title").value = content.title;
    document.getElementById("category").value = content.category;
    document.getElementById("status").value = content.status;
    currentContent = content;
  } else {
    title.textContent = "Tambah Konten";
    form.reset();
    document.getElementById("content-id").value = "";
    currentContent = null;
  }

  modal.style.display = "flex";
}

async function saveContent() {
  const id = document.getElementById("content-id").value;
  const thumbnail = document.getElementById("thumbnail").value.trim();
  const title = document.getElementById("title").value.trim();
  const category = document.getElementById("category").value;
  const status = document.getElementById("status").value;

  // Validasi lebih ketat
  if (!thumbnail || !thumbnail.startsWith("http")) {
    showErrorNotification("Thumbnail harus berupa URL yang valid!");
    return;
  }

  if (!title || title.length < 5) {
    showErrorNotification("Judul harus diisi dan minimal 5 karakter!");
    return;
  }

  if (!category || !status) {
    showErrorNotification("Kategori dan status harus diisi!");
    return;
  }

  try {
    showLoading();
    const method = id ? "PUT" : "POST";
    const url = `api.php?entity=content${id ? `&id=${id}` : ""}`;

    const response = await fetch(url, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ thumbnail, title, category, status }),
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
      throw new Error(data.message || "Gagal menyimpan konten");
    }

    showSuccessNotification(
      id ? "Konten berhasil diperbarui" : "Konten berhasil ditambahkan"
    );
    closeModal("content-modal");
    await loadContent();
    await loadDashboardData();
  } catch (error) {
    console.error("Error saving content:", error);
    showErrorNotification(`Gagal menyimpan konten: ${error.message}`);
  } finally {
    hideLoading();
  }
}

async function editContent(id) {
  try {
    showLoading();
    const response = await fetch(`api.php?entity=content&id=${id}`);

    if (!response.ok) {
      throw new Error("Failed to fetch content");
    }

    const data = await response.json();

    if (data.success) {
      openContentModal(data.data);
    } else {
      throw new Error(data.message || "Content not found");
    }
  } catch (error) {
    console.error("Error editing content:", error);
    showErrorNotification("Gagal memuat data konten");
  } finally {
    hideLoading();
  }
}

async function deleteContent(id) {
  if (!confirm("Apakah Anda yakin ingin menghapus konten ini?")) {
    return;
  }

  try {
    showLoading();
    const response = await fetch(`api.php?entity=content&id=${id}`, {
      method: "DELETE",
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
      throw new Error(data.message || "Failed to delete content");
    }

    showSuccessNotification("Konten berhasil dihapus");
    await loadContent();
    await loadDashboardData();
  } catch (error) {
    console.error("Error deleting content:", error);
    showErrorNotification("Gagal menghapus konten");
  } finally {
    hideLoading();
  }
}

// Modal functions
function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none";
}

window.onclick = function (event) {
  if (event.target.className === "modal") {
    event.target.style.display = "none";
  }
};

// Loading functions
function showLoading() {
  document.getElementById("loading-overlay").style.display = "flex";
}

function hideLoading() {
  document.getElementById("loading-overlay").style.display = "none";
}

// Notifikasi
function showSuccessNotification(message) {
  const toast = document.createElement("div");
  toast.className = "toast-notification success";
  toast.innerHTML = `
    <i class="fas fa-check-circle"></i>
    <span>${message}</span>
  `;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("show");
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 3000);
  }, 100);
}

function showErrorNotification(message) {
  const toast = document.createElement("div");
  toast.className = "toast-notification error";
  toast.innerHTML = `
    <i class="fas fa-exclamation-circle"></i>
    <span>${message}</span>
  `;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("show");
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 3000);
  }, 100);
}
