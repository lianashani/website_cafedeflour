let menuIndex = 1;
document.getElementById('add-menu').addEventListener('click', function () {
  const container = document.getElementById('menu-container');
  const newMenu = document.createElement('div');
  newMenu.classList.add('menu-item', 'mt-3');
  newMenu.innerHTML = `
    <select name="menu[${menuIndex}][id_menu]" class="form-control" required>
      <option value="">Pilih Menu</option>
      <?php
      $menuResult = mysqli_query($conn, $menuQuery); // Refresh menuResult for each new menu
      while ($menu = mysqli_fetch_assoc($menuResult)): ?>
        <option value="<?= $menu['id_menu'] ?>"><?= htmlspecialchars($menu['nama_menu']) ?> - Rp<?= number_format($menu['harga'], 0, ',', '.') ?></option>
      <?php endwhile; ?>
    </select>
    <input type="number" name="menu[${menuIndex}][jumlah]" class="form-control mt-2" placeholder="Jumlah" min="1" required>
  `;
  container.appendChild(newMenu);
  menuIndex++;
});