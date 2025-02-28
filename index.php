<?php
$htdocsPath = dirname(__FILE__);
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'];

function scanDirectory($dir, $baseUrl, $htdocsPath, $relativePath = '')
{
	$projects = [];
	$entries = scandir($dir);

	foreach ($entries as $entry) {
		if ($entry === '.' || $entry === '..' || $entry === 'dashboard') {
			continue;
		}

		$path = $dir . '/' . $entry;
		$relPath = $relativePath . '/' . $entry;
		$url = $baseUrl . $relPath;

		if (is_dir($path)) {
			$hasComposer = file_exists($path . '/composer.json');
			$favicon = '';

			$faviconPaths = [
				$path . '/favicon.ico',
				$path . '/favicon.png',
				$path . '/images/favicon.ico',
				$path . '/images/favicon.png',
				$path . '/assets/favicon.ico',
				$path . '/assets/favicon.png',
				$path . '/public/favicon.ico',
				$path . '/public/favicon.png'
			];

			foreach ($faviconPaths as $faviconPath) {
				if (file_exists($faviconPath)) {
					$favicon = str_replace($htdocsPath, $baseUrl, $faviconPath);
					break;
				}
			}

			$subfolders = [];
			$subdirs = scandir($path);
			foreach ($subdirs as $subdir) {
				if ($subdir !== '.' && $subdir !== '..' && is_dir($path . '/' . $subdir)) {
					$subfolders[] = [
						'name' => $subdir,
						'path' => $relPath . '/' . $subdir,
						'url' => $url . '/' . $subdir
					];
				}
			}

			$projectName = $entry;
			if ($hasComposer) {
				$composerData = json_decode(file_get_contents($path . '/composer.json'), true);
				if (isset($composerData['name'])) {
					$projectName = $composerData['name'];
				}
			}

			$projects[] = [
				'name' => $projectName,
				'path' => $relPath,
				'url' => $url,
				'hasComposer' => $hasComposer,
				'favicon' => $favicon,
				'subfolders' => $subfolders
			];
		}
	}

	return $projects;
}

$projects = scanDirectory($htdocsPath, $baseUrl, $htdocsPath);

usort($projects, function ($a, $b) {
	if ($a['hasComposer'] && !$b['hasComposer']) return -1;
	if (!$a['hasComposer'] && $b['hasComposer']) return 1;
	return strcasecmp($a['name'], $b['name']);
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>NEO XAMPP Dashboard</title>
	<link rel="icon" type="image/png" href="data:image/png;base64,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">	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<style>
		:root {
			--primary: #444;
			--primary-light: #666;
			--primary-dark: #333;
			--accent: #0cf;
			--accent-alt: #fc0;
			--dark: #222;
			--darker: #111;
			--light: #999;
			--white: #eee;
			--neon-glow: 0 0 10px rgba(0, 204, 255, 0.8), 0 0 20px rgba(0, 204, 255, 0.4);
			--neon-glow-alt: 0 0 10px rgba(255, 204, 0, 0.8), 0 0 20px rgba(255, 204, 0, 0.4);
		}

		@font-face {
			font-family: 'Orbitron';
			src: url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&display=swap');
		}

		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Orbitron', 'Segoe UI', sans-serif;
		}

		body {
			background-color: var(--darker);
			color: var(--white);
			overflow-x: hidden;
			perspective: 1000px;
		}

		.background {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: -1;
			overflow: hidden;
		}

		.grid {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background:
				linear-gradient(to right, rgba(0, 204, 255, 0.1) 1px, transparent 1px),
				linear-gradient(to bottom, rgba(0, 204, 255, 0.1) 1px, transparent 1px);
			background-size: 50px 50px;
			transform: perspective(500px) rotateX(60deg);
			transform-origin: center top;
		}

		.particles {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
		}

		.particle {
			position: absolute;
			background-color: var(--accent);
			border-radius: 50%;
			opacity: 0.6;
			animation: float 15s infinite linear;
		}

		@keyframes float {
			0% {
				transform: translateY(0) translateX(0);
				opacity: 0;
			}

			10% {
				opacity: 0.8;
			}

			90% {
				opacity: 0.4;
			}

			100% {
				transform: translateY(-100vh) translateX(20px);
				opacity: 0;
			}
		}

		.dashboard {
			max-width: 1400px;
			margin: 0 auto;
			padding: 30px 20px;
			position: relative;
			z-index: 1;
		}

		header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 40px;
			padding: 20px;
			border-radius: 10px;
			background: linear-gradient(145deg, var(--primary-dark), var(--primary));
			box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
			backdrop-filter: blur(5px);
			position: relative;
			overflow: hidden;
		}

		header::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 2px;
			background: linear-gradient(90deg, transparent, var(--accent), transparent);
			animation: scan 4s infinite linear;
		}

		@keyframes scan {
			0% {
				transform: translateX(-100%);
			}

			100% {
				transform: translateX(100%);
			}
		}

		.logo {
			display: flex;
			align-items: center;
			gap: 15px;
			position: relative;
		}

		.logo h1 {
			font-size: 24px;
			color: var(--white);
			text-transform: uppercase;
			letter-spacing: 2px;
			position: relative;
		}

		.logo h1::after {
			content: '';
			position: absolute;
			bottom: -5px;
			left: 0;
			width: 100%;
			height: 2px;
			background: var(--accent);
			box-shadow: var(--neon-glow);
		}

		.logo img {
			height: 40px;
			filter: drop-shadow(0 0 5px var(--accent));
			animation: pulse 3s infinite alternate;
		}

		@keyframes pulse {
			0% {
				filter: drop-shadow(0 0 2px var(--accent));
			}

			100% {
				filter: drop-shadow(0 0 8px var(--accent));
			}
		}

		.search-bar {
			flex-grow: 1;
			max-width: 500px;
			margin: 0 30px;
			position: relative;
		}

		.search-bar input {
			width: 100%;
			padding: 12px 20px;
			background-color: rgba(0, 0, 0, 0.3);
			border: 1px solid var(--primary-light);
			border-radius: 30px;
			color: var(--white);
			font-size: 14px;
			outline: none;
			transition: all 0.3s ease;
		}

		.search-bar input:focus {
			border-color: var(--accent);
			box-shadow: var(--neon-glow);
		}

		.search-bar::before {
			content: '\f002';
			font-family: 'Font Awesome 6 Free';
			font-weight: 900;
			position: absolute;
			right: 15px;
			top: 50%;
			transform: translateY(-50%);
			color: var(--accent);
		}

		.tools {
			display: flex;
			gap: 15px;
		}

		.tools a {
			display: inline-block;
			padding: 10px 20px;
			background: linear-gradient(145deg, var(--primary), var(--primary-dark));
			color: var(--white);
			text-decoration: none;
			border-radius: 30px;
			border: 1px solid transparent;
			text-transform: uppercase;
			font-size: 12px;
			letter-spacing: 1px;
			transition: all 0.3s ease;
			position: relative;
			overflow: hidden;
		}

		.tools a::before {
			content: '';
			position: absolute;
			top: 0;
			left: -100%;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
			transition: 0.5s;
		}

		.tools a:hover {
			border-color: var(--accent);
			box-shadow: var(--neon-glow);
			transform: translateY(-3px);
		}

		.tools a:hover::before {
			left: 100%;
		}

		.tools a i {
			margin-right: 8px;
			color: var(--accent);
		}

		.projects {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
			gap: 25px;
		}

		.project-card {
			background: linear-gradient(145deg, var(--primary-dark), var(--primary));
			border-radius: 15px;
			overflow: hidden;
			transition: all 0.4s ease;
			transform-style: preserve-3d;
			box-shadow: 0 7px 15px rgba(0, 0, 0, 0.3);
			position: relative;
		}

		.project-card::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: linear-gradient(45deg, transparent 95%, var(--accent) 95%);
			z-index: -1;
			opacity: 0.2;
		}

		.project-card:hover {
			transform: translateY(-10px) rotateY(5deg);
			box-shadow: 10px 20px 30px rgba(0, 0, 0, 0.4);
		}

		.project-card:hover::after {
			opacity: 1;
		}

		.project-card::after {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg,
					transparent,
					transparent,
					rgba(0, 204, 255, 0.1),
					transparent,
					transparent);
			transform: translateX(-100%);
			animation: shimmer 3s infinite;
			opacity: 0;
			transition: opacity 0.3s ease;
		}

		@keyframes shimmer {
			100% {
				transform: translateX(100%);
			}
		}

		.project-header {
			background: linear-gradient(145deg, var(--dark), var(--darker));
			padding: 18px;
			display: flex;
			align-items: center;
			gap: 12px;
			position: relative;
			overflow: hidden;
		}

		.project-header::before {
			content: '';
			position: absolute;
			width: 150%;
			height: 150%;
			background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
			top: -25%;
			left: -25%;
			opacity: 0.1;
			transform: scale(0);
			transition: transform 0.5s ease;
		}

		.project-card:hover .project-header::before {
			transform: scale(1);
		}

		.project-favicon {
			width: 24px;
			height: 24px;
			object-fit: contain;
			background-color: var(--white);
			border-radius: 6px;
			padding: 2px;
			box-shadow: 0 0 10px rgba(0, 204, 255, 0.6);
			transform: rotate(0);
			transition: transform 0.5s ease;
		}

		.project-card:hover .project-favicon {
			transform: rotate(360deg);
		}

		.project-name {
			font-size: 16px;
			font-weight: 500;
			flex-grow: 1;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			text-transform: uppercase;
			letter-spacing: 1px;
		}

		.composer-badge {
			background: linear-gradient(90deg, var(--accent), var(--accent-alt));
			color: var(--darker);
			font-size: 10px;
			padding: 4px 8px;
			border-radius: 20px;
			font-weight: bold;
			box-shadow: var(--neon-glow);
			text-transform: uppercase;
			letter-spacing: 1px;
		}

		.project-body {
			padding: 20px;
		}

		.project-path {
			color: var(--light);
			font-size: 12px;
			margin-bottom: 15px;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			padding: 5px 10px;
			background-color: rgba(0, 0, 0, 0.2);
			border-radius: 5px;
			border-left: 2px solid var(--accent);
		}

		.subfolder-list {
			margin-top: 20px;
			position: relative;
		}

		.subfolder-list select {
			width: 100%;
			padding: 10px 15px;
			background-color: rgba(0, 0, 0, 0.2);
			border: 1px solid var(--primary-light);
			border-radius: 5px;
			color: var(--white);
			font-size: 14px;
			appearance: none;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.subfolder-list select:focus {
			border-color: var(--accent);
			box-shadow: var(--neon-glow);
		}

		.subfolder-list::after {
			content: '\f107';
			font-family: 'Font Awesome 6 Free';
			font-weight: 900;
			position: absolute;
			right: 15px;
			top: 10px;
			color: var(--accent);
			pointer-events: none;
		}

		.subfolder-list button {
			width: 100%;
			padding: 10px;
			margin-top: 10px;
			background: linear-gradient(145deg, var(--primary), var(--primary-dark));
			color: var(--white);
			border: 1px solid transparent;
			border-radius: 5px;
			cursor: pointer;
			transition: all 0.3s ease;
			text-transform: uppercase;
			letter-spacing: 1px;
			font-size: 12px;
			position: relative;
			overflow: hidden;
		}

		.subfolder-list button::before {
			content: '';
			position: absolute;
			top: 0;
			left: -100%;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
			transition: 0.5s;
		}

		.subfolder-list button:hover {
			border-color: var(--accent);
			box-shadow: var(--neon-glow);
		}

		.subfolder-list button:hover::before {
			left: 100%;
		}

		.project-actions {
			display: flex;
			gap: 10px;
			margin-top: 20px;
		}

		.project-actions a {
			flex: 1;
			display: inline-block;
			padding: 12px 5px;
			text-align: center;
			text-decoration: none;
			color: var(--white);
			background: linear-gradient(145deg, var(--primary), var(--primary-dark));
			border-radius: 5px;
			font-size: 13px;
			text-transform: uppercase;
			letter-spacing: 1px;
			transition: all 0.3s ease;
			position: relative;
			overflow: hidden;
			border: 1px solid transparent;
		}

		.project-actions a::before {
			content: '';
			position: absolute;
			top: 0;
			left: -100%;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
			transition: 0.5s;
		}

		.project-actions a.open {
			background: linear-gradient(145deg, var(--primary-dark), var(--primary));
			border-left: 2px solid var(--accent);
		}

		.project-actions a:hover {
			transform: translateY(-3px);
			box-shadow: var(--neon-glow);
			border-color: var(--accent);
		}

		.project-actions a:hover::before {
			left: 100%;
		}

		.project-actions a.open:hover {
			box-shadow: var(--neon-glow);
		}

		.project-actions a.code:hover {
			box-shadow: var(--neon-glow-alt);
			border-color: var(--accent-alt);
		}

		.project-actions a i {
			margin-right: 5px;
			color: var(--accent);
		}

		.project-actions a.code i {
			color: var(--accent-alt);
		}

		.empty-state {
			grid-column: 1 / -1;
			text-align: center;
			padding: 60px;
			background: linear-gradient(145deg, var(--primary-dark), var(--primary));
			border-radius: 15px;
			box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
			position: relative;
			overflow: hidden;
		}

		.empty-state::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background:
				radial-gradient(circle at 20% 30%, rgba(0, 204, 255, 0.1) 0%, transparent 50%),
				radial-gradient(circle at 80% 70%, rgba(255, 204, 0, 0.1) 0%, transparent 50%);
		}

		.empty-state i {
			font-size: 60px;
			color: var(--accent);
			margin-bottom: 30px;
			filter: drop-shadow(var(--neon-glow));
			animation: float-icon 4s infinite ease-in-out;
		}

		@keyframes float-icon {

			0%,
			100% {
				transform: translateY(0);
			}

			50% {
				transform: translateY(-15px);
			}
		}

		.empty-state h2 {
			font-size: 24px;
			margin-bottom: 15px;
			color: var(--white);
			text-transform: uppercase;
			letter-spacing: 2px;
		}

		.empty-state p {
			color: var(--light);
		}

		@media (max-width: 768px) {
			header {
				flex-direction: column;
				align-items: flex-start;
				gap: 15px;
			}

			.search-bar {
				margin: 15px 0;
				max-width: 100%;
			}

			.tools {
				width: 100%;
				justify-content: space-between;
			}

			.tools a {
				flex: 1;
				text-align: center;
				padding: 10px;
				font-size: 11px;
			}

			.tools a i {
				margin-right: 5px;
			}
		}
	</style>
</head>

<body>
	<div class="background">
		<div class="grid"></div>
		<div class="particles" id="particles"></div>
	</div>

	<div class="dashboard">
		<header>
			<div class="logo">
				<img src="<?php echo $baseUrl; ?>/dashboard/xampp-logo.svg" alt="XAMPP Logo" onerror="this.src='<?php echo $baseUrl; ?>/dashboard/favicon.ico'">
				<h1>Neo XAMPP</h1>
			</div>
			<div class="search-bar">
				<input type="text" id="projectSearch" placeholder="SEARCH PROJECTS...">
			</div>
			<div class="tools">
				<a href="<?php echo $baseUrl; ?>/phpmyadmin" target="_blank"><i class="fas fa-database"></i> Database</a>
				<a href="<?php echo $baseUrl; ?>/dashboard" target="_blank"><i class="fas fa-tachometer-alt"></i> XAMPP Panel</a>
			</div>
		</header>

		<div class="projects" id="projectsContainer">
			<?php if (empty($projects)): ?>
				<div class="empty-state">
					<i class="fas fa-folder-open"></i>
					<h2>No Projects Found</h2>
					<p>Place your web projects in the htdocs folder to see them here.</p>
				</div>
			<?php else: ?>
				<?php foreach ($projects as $project): ?>
					<div class="project-card" data-project-name="<?php echo strtolower($project['name']); ?>">
						<div class="project-header">
							<?php if (!empty($project['favicon'])): ?>
								<img src="<?php echo $project['favicon']; ?>" alt="Favicon" class="project-favicon">
							<?php else: ?>
								<i class="fas fa-folder" style="color: var(--accent);"></i>
							<?php endif; ?>
							<div class="project-name"><?php echo htmlspecialchars($project['name']); ?></div>
							<?php if ($project['hasComposer']): ?>
								<span class="composer-badge">PHP</span>
							<?php endif; ?>
						</div>
						<div class="project-body">
							<div class="project-path" title="<?php echo htmlspecialchars($project['path']); ?>">
								<?php echo htmlspecialchars($project['path']); ?>
							</div>

							<?php if (!empty($project['subfolders'])): ?>
								<div class="subfolder-list">
									<select id="subfolder-<?php echo md5($project['path']); ?>">
										<option value="">SELECT SUBFOLDER...</option>
										<?php foreach ($project['subfolders'] as $subfolder): ?>
											<option value="<?php echo htmlspecialchars($subfolder['url']); ?>">
												<?php echo htmlspecialchars($subfolder['name']); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<button onclick="openSubfolder('subfolder-<?php echo md5($project['path']); ?>')">NAVIGATE</button>
								</div>
							<?php endif; ?>

							<div class="project-actions">
								<a href="<?php echo htmlspecialchars($project['url']); ?>" target="_blank" class="open">
									<i class="fas fa-external-link-alt"></i> LAUNCH
								</a>
								<a href="vscode://file/<?php echo htmlspecialchars($htdocsPath . $project['path']); ?>" class="code">
									<i class="fas fa-code"></i> EDIT
								</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<script>
		function createParticles() {
			const particlesContainer = document.getElementById('particles');
			const particleCount = 30;

			for (let i = 0; i < particleCount; i++) {
				const particle = document.createElement('div');
				particle.classList.add('particle');

				const size = Math.random() * 5 + 1;
				particle.style.width = size + 'px';
				particle.style.height = size + 'px';

				particle.style.left = Math.random() * 100 + '%';
				particle.style.top = Math.random() * 100 + '%';

				const duration = Math.random() * 10 + 10;
				const delay = Math.random() * 15;
				particle.style.animationDuration = duration + 's';
				particle.style.animationDelay = delay + 's';

				particle.style.opacity = Math.random() * 0.5 + 0.1;

				particlesContainer.appendChild(particle);
			}
		}

		function addScanLines() {
			const projectCards = document.querySelectorAll('.project-card');

			projectCards.forEach((card, index) => {
				setTimeout(() => {
					card.classList.add('scanned');
				}, index * 200);
			});
		}

		const searchInput = document.getElementById('projectSearch');
		const projectsContainer = document.getElementById('projectsContainer');
		const projectCards = document.querySelectorAll('.project-card');

		searchInput.addEventListener('input', function() {
			const searchTerm = this.value.toLowerCase();

			projectCards.forEach(card => {
				const projectName = card.getAttribute('data-project-name');

				if (projectName.includes(searchTerm)) {
					card.style.display = '';
				} else {
					card.style.display = 'none';
				}
			});
		});

		document.addEventListener('mousemove', function(e) {
			const grid = document.querySelector('.grid');
			const mouseX = e.clientX / window.innerWidth - 0.5;
			const mouseY = e.clientY / window.innerHeight - 0.5;

			grid.style.transform = `perspective(500px) rotateX(${60 + mouseY * 5}deg) rotateY(${mouseX * 5}deg)`;
		});

		function openSubfolder(selectId) {
			const select = document.getElementById(selectId);
			const selectedUrl = select.value;

			if (selectedUrl) {
				select.style.boxShadow = 'var(--neon-glow)';
				select.style.borderColor = 'var(--accent)';

				setTimeout(() => {
					window.open(selectedUrl, '_blank');
					setTimeout(() => {
						select.style.boxShadow = '';
						select.style.borderColor = '';
					}, 500);
				}, 300);
			}
		}

		document.addEventListener('DOMContentLoaded', function() {
			createParticles();
			addScanLines();

			const searchInput = document.getElementById('projectSearch');
			const originalPlaceholder = searchInput.placeholder;
			searchInput.placeholder = '';

			let i = 0;
			const typingInterval = setInterval(() => {
				if (i < originalPlaceholder.length) {
					searchInput.placeholder += originalPlaceholder.charAt(i);
					i++;
				} else {
					clearInterval(typingInterval);
				}
			}, 100);

			searchInput.addEventListener('focus', function() {
				this.style.boxShadow = 'var(--neon-glow)';
			});

			searchInput.addEventListener('blur', function() {
				this.style.boxShadow = '';
				this.style.boxShadow = '';
			});

			const interactiveElements = document.querySelectorAll('a, button');
			interactiveElements.forEach(element => {
				element.addEventListener('mouseenter', function() {
					this.style.boxShadow = 'var(--neon-glow)';
					this.style.borderColor = 'var(--accent)';
				});

				element.addEventListener('mouseleave', function() {
					this.style.boxShadow = '';
					this.style.borderColor = '';
				});
			});

			const projectNames = document.querySelectorAll('.project-name');
			projectNames.forEach(name => {
				const originalText = name.textContent;

				name.addEventListener('mouseenter', function() {
					let iterations = 0;

					const interval = setInterval(() => {
						name.textContent = originalText
							.split('')
							.map((letter, index) => {
								if (index < iterations) {
									return originalText[index];
								}

								return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' [Math.floor(Math.random() * 36)];
							})
							.join('');

						if (iterations >= originalText.length) {
							clearInterval(interval);
							name.textContent = originalText;
						}

						iterations += 1 / 3;
					}, 30);
				});
			});
		});

		if (document.querySelector('.empty-state')) {
			const emptyStateText = document.querySelector('.empty-state p');
			const text = emptyStateText.textContent;
			emptyStateText.textContent = '';

			let i = 0;
			const typingInterval = setInterval(() => {
				if (i < text.length) {
					emptyStateText.textContent += text[i];
					i++;
				} else {
					clearInterval(typingInterval);
					emptyStateText.innerHTML += '<span class="cursor">|</span>';

					setInterval(() => {
						const cursor = document.querySelector('.cursor');
						if (cursor) {
							cursor.style.opacity = cursor.style.opacity === '0' ? '1' : '0';
						}
					}, 500);
				}
			}, 50);
		}
	</script>
</body>

</html>