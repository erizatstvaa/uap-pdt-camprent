<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'CampRent' ?> - CampRent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --forest: #1a3a2a;
            --forest-mid: #2d5a3d;
            --forest-light: #3d7a52;
            --moss: #5a8a5a;
            --earth: #8B6914;
            --earth-light: #c49a2a;
            --cream: #f8f5f0;
            --white: #ffffff;
            --text: #1a2e1a;
            --text-muted: #5a6b5a;
            --border: #d4e4d4;
            --danger: #c0392b;
            --warning: #e67e22;
            --info: #2980b9;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.10);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.12);
            --radius: 12px;
            --radius-sm: 8px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f4f0;
            color: var(--text);
            min-height: 100vh;
        }

        /* ---- SIDEBAR ---- */
        .sidebar {
            position: fixed;
            left: 0; top: 0; bottom: 0;
            width: 240px;
            background: var(--forest);
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }

        .sidebar-logo {
            padding: 28px 24px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo .logo-icon {
            font-size: 28px;
            margin-bottom: 6px;
        }

        .sidebar-logo h1 {
            font-family: 'Sora', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.3px;
        }

        .sidebar-logo .tagline {
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            margin-top: 2px;
        }

        .sidebar-user {
            padding: 16px 24px;
            background: rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-user .user-role {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--earth-light);
            font-weight: 600;
        }

        .sidebar-user .user-name {
            font-size: 14px;
            color: white;
            font-weight: 500;
            margin-top: 2px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 0;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.3);
            padding: 8px 24px 4px;
            font-weight: 600;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 24px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.08);
            color: white;
            border-left-color: var(--earth-light);
        }

        .nav-link i { width: 18px; font-size: 15px; }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 10px 0;
            transition: color 0.2s;
        }

        .btn-logout:hover { color: #ff6b6b; }

        /* ---- MAIN CONTENT ---- */
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border);
        }

        .topbar-title {
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--forest);
        }

        .topbar-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--cream);
            border: 1px solid var(--border);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .page-body {
            padding: 28px 32px;
        }

        /* ---- COMPONENTS ---- */
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }

        .card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .card-title {
            font-family: 'Sora', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--forest);
        }

        .card-body { padding: 24px; }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px 22px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-icon.green { background: #e8f5e9; color: var(--forest-mid); }
        .stat-icon.amber { background: #fff8e1; color: var(--earth); }
        .stat-icon.red { background: #fce4ec; color: var(--danger); }
        .stat-icon.blue { background: #e3f2fd; color: var(--info); }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .stat-value {
            font-family: 'Sora', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--forest);
            line-height: 1.1;
            margin-top: 4px;
        }

        /* Table */
        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead th {
            background: #f8faf8;
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 2px solid var(--border);
            text-align: left;
            white-space: nowrap;
        }

        tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f4f0;
            vertical-align: middle;
        }

        tbody tr:hover { background: #f8faf8; }
        tbody tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .badge-green { background: #e8f5e9; color: #2e7d32; }
        .badge-amber { background: #fff3e0; color: #e65100; }
        .badge-red { background: #fce4ec; color: #c62828; }
        .badge-blue { background: #e3f2fd; color: #1565c0; }
        .badge-gray { background: #f5f5f5; color: #616161; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.18s;
            font-family: 'DM Sans', sans-serif;
        }

        .btn-primary { background: var(--forest-mid); color: white; }
        .btn-primary:hover { background: var(--forest); }
        .btn-success { background: #27ae60; color: white; }
        .btn-success:hover { background: #219a52; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #a93226; }
        .btn-warning { background: var(--warning); color: white; }
        .btn-warning:hover { background: #d35400; }
        .btn-outline { background: white; color: var(--forest); border: 1px solid var(--border); }
        .btn-outline:hover { background: #f0f4f0; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background: white;
            transition: border-color 0.2s;
            outline: none;
        }

        .form-control:focus { border-color: var(--forest-light); }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* Alert */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-success { background: #e8f5e9; color: #1b5e20; border-left: 4px solid #27ae60; }
        .alert-danger { background: #fce4ec; color: #880e4f; border-left: 4px solid #c62828; }
        .alert-warning { background: #fff8e1; color: #e65100; border-left: 4px solid #f57f17; }
        .alert-info { background: #e3f2fd; color: #0d47a1; border-left: 4px solid #1976d2; }

        /* Divider */
        .section-gap { margin-bottom: 24px; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--text-muted);
        }

        .empty-state i { font-size: 48px; opacity: 0.3; margin-bottom: 12px; }
        .empty-state p { font-size: 14px; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
    </style>