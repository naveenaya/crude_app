<?php
require 'db.php';

// (Optional) Protect the page if login is required
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

$perPage = 5; // posts per page
$search  = isset($_GET['search']) ? trim($_GET['search']) : '';
$page    = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;

// ----- COUNT TOTAL -----
if ($search !== '') {
    $sqlCount = "SELECT COUNT(*) AS total FROM posts
                 WHERE title LIKE ? OR content LIKE ?";
    $stmt = $conn->prepare($sqlCount);
    $like = "%{$search}%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $sqlCount = "SELECT COUNT(*) AS total FROM posts";
    $stmt = $conn->prepare($sqlCount);
}
$stmt->execute();
$countRes = $stmt->get_result()->fetch_assoc();
$totalRows = (int)($countRes['total'] ?? 0);
$stmt->close();

$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

// ----- FETCH POSTS -----
if ($search !== '') {
    $sql = "SELECT id, title, content, created_at
            FROM posts
            WHERE title LIKE ? OR content LIKE ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $like, $like, $perPage, $offset);
} else {
    $sql = "SELECT id, title, content, created_at
            FROM posts
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $perPage, $offset);
}
$stmt->execute();
$posts = $stmt->get_result();

// Helper function for pagination links
function pageLink($pageNum, $search) {
    $params = [];
    if ($search !== '') $params['search'] = $search;
    $params['page'] = $pageNum;
    return 'posts.php?' . http_build_query($params);
}
?>

<?php include 'header.php'; ?>

<div class="container">
  <h2 class="mb-4">📖 Blog Posts</h2>

  <!-- Search Form -->
  <form class="row g-2 mb-4" method="get" action="posts.php">
    <div class="col-md-8">
      <input
        type="text"
        name="search"
        class="form-control"
        placeholder="Search by title or content..."
        value="<?= htmlspecialchars($search) ?>"
      >
    </div>
    <div class="col-md-4 d-flex gap-2">
      <button class="btn btn-primary" type="submit">🔍 Search</button>
      <a class="btn btn-outline-secondary" href="posts.php">Reset</a>
    </div>
  </form>

  <?php if ($posts->num_rows === 0): ?>
    <div class="alert alert-info">No posts found.</div>
  <?php else: ?>
    <?php while ($row = $posts->fetch_assoc()): ?>
      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
          <h6 class="card-subtitle text-muted mb-2">
            <?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at']))) ?>
          </h6>
          <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
          <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?= $row['id'] ?>">✏ Edit</a>
            <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?= $row['id'] ?>"
               onclick="return confirm('Delete this post?');">🗑 Delete</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <nav aria-label="Posts pages">
    <ul class="pagination justify-content-center">
      <!-- Previous -->
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= ($page > 1) ? pageLink($page - 1, $search) : '#' ?>">Previous</a>
      </li>

      <!-- Page Numbers -->
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= pageLink($i, $search) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Next -->
      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= ($page < $totalPages) ? pageLink($page + 1, $search) : '#' ?>">Next</a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>


