<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/EquipmentManager.php';
require_once __DIR__ . '/../classes/UserManager.php';
require_once __DIR__ . '/../classes/TransactionManager.php';

$db = (new Database())->connect();
$equipment = new EquipmentManager($db);
$users = new UserManager($db);
$transactions = new TransactionManager($db);
$message = '';
$error = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'add_equipment') {
        $equipment->add(trim($_POST['name']), trim($_POST['category']), max(0, (int)$_POST['quantity']));
        $message = 'Equipment added successfully.';
    } elseif ($action === 'update_equipment') {
        $equipment->update((int)$_POST['id'], trim($_POST['name']), trim($_POST['category']), max(0, (int)$_POST['quantity']));
        $message = 'Equipment updated successfully.';
    } elseif ($action === 'delete_equipment') {
        $equipment->delete((int)$_POST['id']);
        $message = 'Equipment removed successfully.';
    } elseif ($action === 'add_user') {
        $users->add(trim($_POST['name']), trim($_POST['email']), $_POST['type'], trim($_POST['extra']));
        $message = 'User added successfully.';
    } elseif ($action === 'delete_user') {
        $users->delete((int)$_POST['id']);
        $message = 'User removed successfully.';
    } elseif ($action === 'borrow') {
        $transactions->borrow((int)$_POST['equipment_id'], (int)$_POST['user_id'], (int)$_POST['quantity']);
        $message = 'Equipment borrowed successfully.';
    } elseif ($action === 'return') {
        $transactions->returnItem((int)$_POST['id']);
        $message = 'Equipment returned successfully.';
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$search = trim($_GET['search'] ?? '');
$equipmentRows = $equipment->all($search);
$allEquipmentRows = $search === '' ? $equipmentRows : $equipment->all('');
$userRows = $users->all();
$transactionRows = $transactions->all();

$totalTypes = count($allEquipmentRows);
$totalUnits = array_sum(array_map(fn($row) => (int)$row['quantity'], $allEquipmentRows));
$activeTransactions = count(array_filter($transactionRows, fn($row) => $row['status'] === 'Borrowed'));
$totalUsers = count($userRows);
$availableUnits = $totalUnits;
foreach ($transactionRows as $t) {
    if ($t['status'] === 'Borrowed') $availableUnits -= (int)$t['quantity'];
}
$availableUnits = max(0, $availableUnits);

function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $letters = '';
    foreach (array_slice($parts, 0, 2) as $part) $letters .= strtoupper(substr($part, 0, 1));
    return $letters ?: 'AD';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Campus Equipment | Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark">CE</div>
            <div><strong>Campus Equipment</strong><small>Borrowing System</small></div>
        </div>
        <div class="nav-label">Workspace</div>
        <nav class="nav">
            <a class="active" href="#dashboard"><svg fill="none" viewBox="0 0 24 24"><path d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-12h6V4h-6v4Z" stroke-width="1.8" stroke-linejoin="round"/></svg>Dashboard</a>
            <a href="#equipment"><svg fill="none" viewBox="0 0 24 24"><path d="M4 7h16v13H4V7Zm3-3h10v3H7V4Zm1 7h8m-8 4h5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Equipment</a>
            <a href="#users"><svg fill="none" viewBox="0 0 24 24"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m6-10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7-5a2.5 2.5 0 1 1 0 5m0 3a4 4 0 0 1 3 3.9V20" stroke-width="1.8" stroke-linecap="round"/></svg>Users</a>
            <a href="#borrow"><svg fill="none" viewBox="0 0 24 24"><path d="M6 5h12M6 9h12M6 13h7m-7 4h5m7-2 3 3m0 0-3 3m3-3h-7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Borrow Equipment</a>
            <a href="#transactions"><svg fill="none" viewBox="0 0 24 24"><path d="M7 3h10v18H7V3Zm3 4h4m-4 4h4m-4 4h4" stroke-width="1.8" stroke-linecap="round"/></svg>Transactions</a>
        </nav>
        <div class="side-note"><strong>OOP Final Project</strong><br>Classes, encapsulation, inheritance, abstraction and polymorphism are implemented in the system.</div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:10px">
                <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
                <div><div class="page-title">Management Dashboard</div><div class="page-subtitle">School equipment borrowing and records</div></div>
            </div>
            <div class="profile"><div class="avatar">AD</div><div><strong style="font-size:12px">Administrator</strong><small>System Manager</small></div></div>
        </header>

        <div class="content" id="dashboard">
            <?php if ($message): ?><div class="message success">✓ <?=h($message)?></div><?php endif; ?>
            <?php if ($error): ?><div class="message error">! <?=h($error)?></div><?php endif; ?>

            <section class="hero">
                <div><div class="eyebrow">Campus resource management</div><h1>Good day, Administrator.</h1><p>Monitor equipment, users, and borrowing activity from one place.</p></div>
                <a class="primary-btn" href="#equipment" onclick="openAddEquipment()">＋ Add Equipment</a>
            </section>

            <section class="stats">
                <div class="stat-card"><div class="stat-top"><span>Equipment Types</span><span class="stat-icon"><svg fill="none" viewBox="0 0 24 24"><path d="M4 7h16v13H4V7Zm3-3h10v3H7V4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></span></div><div class="stat-value"><?= $totalTypes ?></div><div class="stat-foot">Registered equipment categories</div></div>
                <div class="stat-card"><div class="stat-top"><span>Available Units</span><span class="stat-icon"><svg fill="none" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div><div class="stat-value"><?= $availableUnits ?></div><div class="stat-foot">Units currently available</div></div>
                <div class="stat-card"><div class="stat-top"><span>Active Borrowings</span><span class="stat-icon"><svg fill="none" viewBox="0 0 24 24"><path d="M7 5h10M7 9h10M7 13h5m4 2 3 3m0 0-3 3m3-3h-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span></div><div class="stat-value"><?= $activeTransactions ?></div><div class="stat-foot">Transactions awaiting return</div></div>
                <div class="stat-card"><div class="stat-top"><span>Registered Users</span><span class="stat-icon"><svg fill="none" viewBox="0 0 24 24"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m6-10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span></div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-foot">Students and staff</div></div>
            </section>

            <div class="layout-grid">
                <div>
                    <section class="panel section" id="equipment">
                        <div class="panel-head"><div><div class="panel-title">Equipment Management</div><div class="panel-subtitle">Add, search, update and remove campus equipment.</div></div><button class="primary-btn" onclick="openAddEquipment()">＋ Add Equipment</button></div>
                        <div class="panel-body">
                            <form method="get" class="search-row"><input class="input" name="search" placeholder="Search by equipment name or category..." value="<?=h($search)?>"><button class="secondary-btn">Search</button><?php if($search):?><a class="secondary-btn" href="index.php#equipment">Clear</a><?php endif;?></form>
                            <div class="table-wrap"><table class="table"><thead><tr><th>Equipment</th><th>Category</th><th>Quantity</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                            <?php if (!$equipmentRows): ?><tr><td colspan="5"><div class="empty">No equipment records found.</div></td></tr><?php endif; ?>
                            <?php foreach($equipmentRows as $e): ?><tr>
                                <td><div class="item-name"><?=h($e['name'])?></div><div class="item-meta">ID #<?=h($e['id'])?></div></td><td><?=h($e['category'])?></td><td><strong><?=h($e['quantity'])?></strong> units</td><td><span class="badge <?=$e['status']==='Available'?'success':'danger'?>"><?=h($e['status'])?></span></td>
                                <td><div class="action-row"><button class="mini-btn edit" type="button" onclick='openEditEquipment(<?=json_encode($e, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>)'>Edit</button><form method="post" onsubmit="return confirm('Delete this equipment?')"><input type="hidden" name="action" value="delete_equipment"><input type="hidden" name="id" value="<?=$e['id']?>"><button class="mini-btn delete">Delete</button></form></div></td>
                            </tr><?php endforeach; ?></tbody></table></div>
                        </div>
                    </section>

                    <section class="panel section" id="users">
                        <div class="panel-head"><div><div class="panel-title">User Management</div><div class="panel-subtitle">Manage students and staff who can borrow equipment.</div></div></div>
                        <div class="panel-body">
                            <form method="post" class="form-grid" style="margin-bottom:20px"><input type="hidden" name="action" value="add_user"><div class="field"><label>Full Name</label><input class="input" name="name" placeholder="e.g. Juan Dela Cruz" required></div><div class="field"><label>Email</label><input class="input" type="email" name="email" placeholder="name@example.com" required></div><div class="field"><label>User Type</label><select class="select" name="type"><option>Student</option><option>Staff</option></select></div><div class="field"><label>Course / Department</label><input class="input" name="extra" placeholder="e.g. BSIT" required></div><div class="field"><button class="primary-btn" style="margin-top:17px">Add User</button></div></form>
                            <div class="table-wrap"><table class="table"><thead><tr><th>User</th><th>Type</th><th>Course / Department</th><th>Action</th></tr></thead><tbody>
                            <?php foreach($userRows as $u): $obj=$users->makeObject($u); ?><tr><td><div class="item-name"><?=h($obj->getName())?></div><div class="item-meta"><?=h($obj->getEmail())?></div></td><td><span class="badge <?=$obj->getRole()==='Student'?'success':'warning'?>"><?=h($obj->getRole())?></span></td><td><?=h($u['extra'])?></td><td><form method="post" onsubmit="return confirm('Delete this user?')"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="mini-btn delete">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div>
                        </div>
                    </section>
                </div>

                <div>
                    <section class="panel section" id="borrow">
                        <div class="panel-head"><div><div class="panel-title">Borrow Equipment</div><div class="panel-subtitle">Create a new borrowing transaction.</div></div></div>
                        <div class="panel-body"><form method="post"><input type="hidden" name="action" value="borrow"><div class="field"><label>Equipment</label><select class="select" name="equipment_id" required><option value="">Select equipment</option><?php foreach($allEquipmentRows as $e): if((int)$e['quantity']>0): ?><option value="<?=$e['id']?>"><?=h($e['name'])?> — <?=$e['quantity']?> available</option><?php endif; endforeach; ?></select></div><div class="field" style="margin-top:12px"><label>Borrower</label><select class="select" name="user_id" required><option value="">Select student or staff</option><?php foreach($userRows as $u): ?><option value="<?=$u['id']?>"><?=h($u['name'])?> — <?=h($u['type'])?></option><?php endforeach; ?></select></div><div class="field" style="margin-top:12px"><label>Quantity</label><input class="input" type="number" name="quantity" min="1" value="1" required></div><button class="primary-btn" style="width:100%;justify-content:center;margin-top:16px">Borrow Equipment</button></form></div>
                    </section>

                    <section class="panel"><div class="panel-head"><div><div class="panel-title">Recent Activity</div><div class="panel-subtitle">Latest borrowing records.</div></div><a class="anchor-note" href="#transactions">View all →</a></div><div class="panel-body"><div class="recent-list"><?php $recent=array_slice($transactionRows,0,5); if(!$recent): ?><div class="empty">No transactions yet.</div><?php else: foreach($recent as $t): ?><div class="recent-item"><div class="recent-left"><div class="recent-name"><?=h($t['equipment_name'])?></div><div class="recent-meta"><?=h($t['borrower_name'])?> · <?=h($t['quantity'])?> unit(s)</div></div><span class="badge <?=$t['status']==='Borrowed'?'warning':'success'?>"><?=h($t['status'])?></span></div><?php endforeach; endif; ?></div></div></section>
                </div>
            </div>

            <section class="panel section" id="transactions">
                <div class="panel-head"><div><div class="panel-title">Borrow & Return Transactions</div><div class="panel-subtitle">Complete history of equipment movement.</div></div></div>
                <div class="panel-body"><div class="table-wrap"><table class="table"><thead><tr><th>Equipment</th><th>Borrower</th><th>Qty</th><th>Borrowed</th><th>Status</th><th>Action</th></tr></thead><tbody>
                <?php if(!$transactionRows): ?><tr><td colspan="6"><div class="empty">No transactions recorded.</div></td></tr><?php endif; ?>
                <?php foreach($transactionRows as $t): ?><tr><td><div class="item-name"><?=h($t['equipment_name'])?></div></td><td><div class="item-name"><?=h($t['borrower_name'])?></div></td><td><?=h($t['quantity'])?></td><td><?=h($t['borrow_date'])?></td><td><span class="badge <?=$t['status']==='Borrowed'?'warning':'success'?>"><?=h($t['status'])?></span></td><td><?php if($t['status']==='Borrowed'): ?><form method="post"><input type="hidden" name="action" value="return"><input type="hidden" name="id" value="<?=$t['id']?>"><button class="mini-btn return">Return</button></form><?php else: ?><span class="anchor-note">Completed</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div>
            </section>
            <div style="text-align:center;color:#9aa3b5;font-size:10px;padding-top:4px">Campus Equipment Borrowing System · OOP Final Programming Project</div>
        </div>
    </main>
</div>

<div class="modal" id="equipmentModal" onclick="closeModalOnBackdrop(event)"><div class="modal-card"><div class="modal-head"><strong id="modalTitle">Add Equipment</strong><button class="close" type="button" onclick="closeEquipmentModal()">×</button></div><div class="modal-body"><form method="post" id="equipmentForm"><input type="hidden" name="action" id="equipmentAction" value="add_equipment"><input type="hidden" name="id" id="equipmentId"><div class="field"><label>Equipment Name</label><input class="input" name="name" id="equipmentName" required placeholder="e.g. Laptop"></div><div class="field" style="margin-top:13px"><label>Category</label><input class="input" name="category" id="equipmentCategory" required placeholder="e.g. Computer"></div><div class="field" style="margin-top:13px"><label>Quantity</label><input class="input" type="number" name="quantity" id="equipmentQuantity" min="0" required value="1"></div><div class="modal-actions"><button type="button" class="secondary-btn" onclick="closeEquipmentModal()">Cancel</button><button class="primary-btn" id="modalSubmit">Add Equipment</button></div></form></div></div></div>
<script>
function openAddEquipment(){document.getElementById('modalTitle').textContent='Add Equipment';document.getElementById('equipmentAction').value='add_equipment';document.getElementById('equipmentId').value='';document.getElementById('equipmentName').value='';document.getElementById('equipmentCategory').value='';document.getElementById('equipmentQuantity').value='1';document.getElementById('modalSubmit').textContent='Add Equipment';document.getElementById('equipmentModal').classList.add('show');document.getElementById('equipmentName').focus()}
function openEditEquipment(item){document.getElementById('modalTitle').textContent='Edit Equipment';document.getElementById('equipmentAction').value='update_equipment';document.getElementById('equipmentId').value=item.id;document.getElementById('equipmentName').value=item.name;document.getElementById('equipmentCategory').value=item.category;document.getElementById('equipmentQuantity').value=item.quantity;document.getElementById('modalSubmit').textContent='Save Changes';document.getElementById('equipmentModal').classList.add('show');document.getElementById('equipmentName').focus()}
function closeEquipmentModal(){document.getElementById('equipmentModal').classList.remove('show')}
function closeModalOnBackdrop(e){if(e.target.id==='equipmentModal')closeEquipmentModal()}
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open')}
document.querySelectorAll('.nav a').forEach(a=>a.addEventListener('click',()=>document.getElementById('sidebar').classList.remove('open')));
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeEquipmentModal()});
</script>
</body>
</html>
