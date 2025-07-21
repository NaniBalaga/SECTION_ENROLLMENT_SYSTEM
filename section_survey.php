<?php
session_start();
if (!isset($_SESSION['register_number'])) {
    header("Location: ../login.php");
    exit();
}

$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$register_number = $_SESSION['register_number'];
$sql = "SELECT * FROM students WHERE register_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $register_number);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle join/leave section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['join_section'])) {
        $section = $_POST['section'];

        $insert_sql = "INSERT INTO section_survey (register_number, name, class, section) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssss", $register_number, $user['name'], $user['class'], $section);
        $insert_stmt->execute();
    } elseif (isset($_POST['leave_section'])) {
        $delete_sql = "DELETE FROM section_survey WHERE register_number = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("s", $register_number);
        $delete_stmt->execute();
    }
}

// Check current section
$current_section_sql = "SELECT section FROM section_survey WHERE register_number = ?";
$current_section_stmt = $conn->prepare($current_section_sql);
$current_section_stmt->bind_param("s", $register_number);
$current_section_stmt->execute();
$current_section_result = $current_section_stmt->get_result();
$current_section = $current_section_result->fetch_assoc();

function getAvailableSections($class)
{
    $sectionOptions = [
        "BTech-2-Year-ECE" => ["A", "B", "C"],
        "BTech-2-Year-BBA" => ["A", "B"],
        "BTech-2-Year-OTHER" => ["A"],
        "default" => array_merge(range('A', 'Z'), ["AA", "AB", "AC", "AD", "AE", "AF"])
    ];
    return $sectionOptions[$class] ?? $sectionOptions['default'];
}

$available_sections = getAvailableSections($user['class']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Survey | SRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2.5rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .subtitle {
            color: var(--gray);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Filter Section */
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .filter-label {
            font-weight: 500;
            color: var(--dark);
            white-space: nowrap;
        }

        .filter-select {
            padding: 0.625rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            background-color: white;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--dark);
            cursor: pointer;
            transition: var(--transition);
            min-width: 120px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            background-color: white;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--dark);
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        /* Current Section Banner */
        .current-section {
            background: white;
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(16, 185, 129, 0.1));
            border: none;
            transition: var(--transition);
        }

        .current-section:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .current-section-text {
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
            overflow: hidden;
        }

        .current-section-text span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .current-section-text i {
            color: var(--primary);
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* Button Styles */
        .btn {
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .btn-join {
            background-color: var(--secondary);
            color: white;
        }

        .btn-join:hover {
            background-color: #0d9f6e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-leave {
            background-color: var(--danger);
            color: white;
        }

        .btn-leave:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-view {
            background-color: var(--primary);
            color: white;
        }

        .btn-view:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-disabled {
            background-color: var(--gray-light);
            color: var(--gray);
            cursor: not-allowed;
        }

        /* Sections Grid */
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .section-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .section-title {
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-count {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Member Preview */
        .member-preview {
            margin: 1.25rem 0;
            flex-grow: 1;
        }

        .preview-title {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .preview-avatars {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-start;
        }

        .avatar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 60px;
            gap: 6px;
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid white;
            background-size: cover;
            background-position: center;
            background-color: var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .avatar:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .avatar-name {
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .more-count-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 60px;
            gap: 6px;
        }

        .more-count {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--gray-light);
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .more-text {
            font-size: 0.75rem;
            color: var(--gray);
            text-align: center;
        }

        /* Section Actions */
        .section-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            gap: 0.75rem;
            width: 100%;
        }

        .section-actions form {
            flex: 1;
            min-width: 0;
        }

        .section-actions .btn {
            width: 100%;
            justify-content: center;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            overflow: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            background-color: white;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: var(--radius);
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-lg);
            max-height: 80vh;
            overflow-y: auto;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .close {
            color: var(--gray);
            float: right;
            font-size: 1.75rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: var(--dark);
        }

        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .members-list {
            margin-top: 1.5rem;
        }

        .member-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
            gap: 1rem;
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }

        .member-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 600;
            flex-shrink: 0;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .member-details {
            overflow: hidden;
            min-width: 0;
        }

        .member-name {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .member-regno {
            font-size: 0.875rem;
            color: var(--gray);
            margin-top: 0.125rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .member-profile {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .member-profile:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .no-members {
            color: var(--gray);
            text-align: center;
            padding: 2rem;
            font-size: 1.1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .no-members i {
            font-size: 2.5rem;
            color: var(--gray-light);
        }

        /* Loading Animation */
        .loading {
            display: flex;
            justify-content: center;
            padding: 2rem;
        }

        .loading-spinner {
            width: 2.5rem;
            height: 2.5rem;
            border: 4px solid var(--gray-light);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .gradient-text {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .current-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
            }

            .sections-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                padding: 1.5rem;
                margin: 1rem auto;
            }

            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .gradient-text {
                font-size: 1.75rem;
            }

            .section-actions {
                flex-direction: row;
            }

            .member-item {
                flex-direction: row;
                align-items: center;
                gap: 1rem;
            }

            .member-profile {
                width: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="gradient-text">
                <i class="fas fa-layer-group"></i> Section Enrollment
            </h1>
            <p class="subtitle">
                Select your preferred class section and connect with classmates in your group
            </p>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
            <div class="filter-group">
                <span class="filter-label">Filter by:</span>
                <select class="filter-select" id="sectionFilter">
                    <option value="all">All Sections</option>
                    <?php foreach ($available_sections as $section): ?>
                        <option value="<?php echo $section; ?>">Section <?php echo $section; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="sectionSearch" placeholder="Search sections...">
            </div>
        </div>

        <?php if ($current_section): ?>
            <div class="current-section">
                <span class="current-section-text">
                    <i class="fas fa-check-circle"></i>
                    <span>You are enrolled in <strong>Section <?php echo htmlspecialchars($current_section['section']); ?></strong></span>
                </span>
                <form method="POST">
                    <button type="submit" name="leave_section" class="btn btn-leave">
                        <i class="fas fa-sign-out-alt"></i> Leave Section
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="sections-grid" id="sectionsContainer">
            <?php foreach ($available_sections as $section):
                // Get members for preview (limit 3)
                $preview_sql = "SELECT s.name, s.register_number, s.profile_photo 
                                FROM section_survey ss
                                JOIN students s ON ss.register_number = s.register_number
                                WHERE ss.class = ? AND ss.section = ?
                                ORDER BY s.name ASC
                                LIMIT 3";
                $preview_stmt = $conn->prepare($preview_sql);
                $preview_stmt->bind_param("ss", $user['class'], $section);
                $preview_stmt->execute();
                $preview_result = $preview_stmt->get_result();
                $preview_members = $preview_result->fetch_all(MYSQLI_ASSOC);

                // Get total member count
                $count_sql = "SELECT COUNT(*) as count FROM section_survey WHERE class = ? AND section = ?";
                $count_stmt = $conn->prepare($count_sql);
                $count_stmt->bind_param("ss", $user['class'], $section);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $count = $count_result->fetch_assoc()['count'];

                $more_count = $count - count($preview_members);
            ?>
                <div class="section-card" data-section="<?php echo $section; ?>">
                    <div class="section-header">
                        <span class="section-title">
                            <i class="fas fa-users"></i> Section <?php echo $section; ?>
                        </span>
                        <span class="section-count">
                            <i class="fas fa-user"></i> <?php echo $count; ?>
                        </span>
                    </div>

                    <?php if ($count > 0): ?>
                        <div class="member-preview" style="border: 1px dashed #ccc; padding: 15px; border-radius: 10px; background: rgba(255, 255, 255, 0.02);">
                            <div class="preview-title">
                                <i class="fas fa-user-friends"></i>
                                <span>Classmates in this section:</span>
                            </div>
                            <div class="preview-avatars">
                                <?php foreach ($preview_members as $member):
                                    $profile_photo = !empty($member['profile_photo']) ?
                                        'https://oursrmap.purlyedit.in/' . ltrim($member['profile_photo'], '/') :
                                        '';
                                    $initials = implode('', array_map(function ($n) {
                                        return strtoupper(substr($n, 0, 1));
                                    }, explode(' ', $member['name'])));
                                    $profile_url = "https://oursrmap.purlyedit.in/view_profile?register_number=" . urlencode($member['register_number']);
                                ?>
                                    <a href="<?php echo $profile_url; ?>" target="_blank" class="avatar-container">
                                        <div class="avatar" style="background-image: url('<?php echo $profile_photo; ?>')">
                                            <?php if (empty($profile_photo)) echo $initials; ?>
                                        </div>
                                        <div class="avatar-name">
                                            <?php echo htmlspecialchars($member['name']); ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>

                                <?php if ($more_count > 0): ?>
                                    <div class="more-count-container" title="<?php echo $more_count; ?> more members">
                                        <div class="more-count">
                                            +<?php echo $more_count; ?>
                                        </div>
                                        <div class="more-text">
                                            More
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-section" style="border: 1px dashed #ccc; width: 100%; padding: 10px 15px; border-radius: 8px; background: rgba(255, 255, 255, 0.02); text-align: center; font-size: 13px; color: #aaa;">
                            <p class="empty-message" style="margin: 0;">
                                "<?php echo htmlspecialchars($section); ?>" — No one has joined this section yet.
                            </p>
                        </div>
                    <?php endif; ?>



                    <div class="section-actions">
                        <?php if ($current_section): ?>
                            <?php if ($current_section['section'] === $section): ?>
                                <button class="btn btn-disabled" style="flex: 1;">
                                    <i class="fas fa-check-circle"></i> Enrolled
                                </button>
                            <?php else: ?>
                                <button class="btn btn-disabled" style="flex: 1;">
                                    <i class="fas fa-plus-circle"></i> Join
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="section" value="<?php echo $section; ?>">
                                <button type="submit" name="join_section" class="btn btn-join">
                                    <i class="fas fa-plus-circle"></i> Join Now
                                </button>
                            </form>
                        <?php endif; ?>

                        <button onclick="openModal('<?php echo $section; ?>')" class="btn btn-view" style="flex: 1;">
                            <i class="fas fa-user-friends"></i> View All
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal for viewing members -->
    <div id="membersModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="modal-title">
                <i class="fas fa-users"></i>
                <span id="modalTitle">Section Members</span>
            </h2>
            <div class="members-list" id="membersList">
                <div class="loading">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filter sections
        const sectionFilter = document.getElementById('sectionFilter');
        const sectionSearch = document.getElementById('sectionSearch');
        const sectionsContainer = document.getElementById('sectionsContainer');

        sectionFilter.addEventListener('change', filterSections);
        sectionSearch.addEventListener('input', filterSections);

        function filterSections() {
            const filterValue = sectionFilter.value.toLowerCase();
            const searchValue = sectionSearch.value.toLowerCase();

            Array.from(sectionsContainer.children).forEach(card => {
                const section = card.dataset.section.toLowerCase();
                const matchesFilter = filterValue === 'all' || section === filterValue;
                const matchesSearch = section.includes(searchValue) ||
                    card.textContent.toLowerCase().includes(searchValue);

                if (matchesFilter && matchesSearch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Modal functions
        function openModal(section) {
            const modal = document.getElementById('membersModal');
            document.getElementById('modalTitle').textContent = `Section ${section}`;

            // Show modal with animation
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('show'), 10);

            // Load members via AJAX
            fetch(`fetch_section_survey.php?class=<?php echo urlencode($user['class']); ?>&section=${section}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('membersList').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('membersList').innerHTML = `
                        <div class="no-members">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Failed to load members. Please try again.</p>
                        </div>
                    `;
                });
        }

        function closeModal() {
            const modal = document.getElementById('membersModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.getElementById('membersList').innerHTML = `
                    <div class="loading">
                        <div class="loading-spinner"></div>
                    </div>
                `;
            }, 300);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('membersModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>