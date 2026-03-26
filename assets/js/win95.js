/**
 * Windows 95 Theme JavaScript
 *
 * Handles Start menu, menu bar dropdowns, window dragging,
 * edge resizing, taskbar window management, multi-window,
 * and system clock.
 */
(function () {
	'use strict';

	var TASKBAR_HEIGHT = 28;
	var RESIZE_HANDLE = 6;
	var MIN_WIN_W = 320;
	var MIN_WIN_H = 200;
	var MAX_WINDOWS = 5;
	var CASCADE_OFFSET = 22; // Win95 CW_USEDEFAULT: SM_CXSIZE(18) + SM_CXFRAME(4)
	var CASCADE_INDEX = 0;   // Global cascade counter (resets when overflow)
	var ICON_GUTTER = 88;    // Width reserved for desktop icon column (72px + padding/gap)
	var TITLE_BAR_H = 22;    // Approx title bar height for drag clamping

	// Track all managed windows
	var managedWindows = [];
	var highestZ = 100;

	// --- System Clock ---
	function updateClock() {
		var clock = document.getElementById('system-clock');
		if (!clock) return;
		var now = new Date();
		var hours = now.getHours();
		var minutes = now.getMinutes();
		var ampm = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12 || 12;
		minutes = minutes < 10 ? '0' + minutes : minutes;
		clock.textContent = hours + ':' + minutes + ' ' + ampm;
	}

	// --- Get desktop dimensions (excludes taskbar) ---
	function getDesktopBounds() {
		var desktop = document.getElementById('desktop');
		if (desktop) {
			return {
				width: desktop.clientWidth,
				height: window.innerHeight - TASKBAR_HEIGHT
			};
		}
		return {
			width: window.innerWidth,
			height: window.innerHeight - TASKBAR_HEIGHT
		};
	}

	// --- Position a window using authentic Win95 CW_USEDEFAULT cascade ---
	function positionWindow(win, index) {
		var bounds = getDesktopBounds();

		// Reserve space for desktop icon columns on the left
		// Check if icons are visible (hidden at narrow widths via CSS)
		var iconGrid = document.querySelector('.desktop-icons-grid');
		var hasIcons = iconGrid && iconGrid.offsetWidth > 0;
		var iconGutter = hasIcons ? ICON_GUTTER : 0;

		// Available work area for windows (minus icon gutter)
		var workW = bounds.width - iconGutter;
		var workH = bounds.height;

		// Win95 default size: 3/4 of the available work area
		var winW = Math.max(MIN_WIN_W, Math.floor(workW * 0.75));
		var winH = Math.max(MIN_WIN_H, Math.floor(workH * 0.75));

		// Cap width so it doesn't feel overly wide on large screens
		if (winW > 1000) winW = 1000;

		// Calculate cascade position (origin starts after icon gutter + top gutter)
		var topGutter = 8;
		var cascadeX = iconGutter + (CASCADE_INDEX * CASCADE_OFFSET);
		var cascadeY = topGutter + (CASCADE_INDEX * CASCADE_OFFSET);

		// Win95 cascade reset: if window would overflow work area, reset to origin
		if (cascadeX + winW > bounds.width || cascadeY + winH > bounds.height) {
			CASCADE_INDEX = 0;
			cascadeX = iconGutter;
			cascadeY = topGutter;
		}

		var left = cascadeX;
		var top = cascadeY;

		// Advance the global cascade counter for next window
		CASCADE_INDEX++;

		win.style.position = 'absolute';
		win.style.left = left + 'px';
		win.style.top = top + 'px';
		win.style.width = winW + 'px';
		win.style.height = winH + 'px';
	}

	// --- Bring window to front ---
	function bringToFront(win) {
		// Deactivate all windows
		managedWindows.forEach(function (mw) {
			mw.win.classList.remove('is-active');
			if (mw.btn) mw.btn.classList.remove('is-active');
			if (mw.btn) mw.btn.classList.add('is-minimized');
		});

		// Activate this window
		highestZ++;
		win.style.zIndex = highestZ;
		win.classList.add('is-active');

		var mw = getManagedWindow(win);
		if (mw && mw.btn) {
			mw.btn.classList.add('is-active');
			mw.btn.classList.remove('is-minimized');
		}
	}

	function getManagedWindow(win) {
		for (var i = 0; i < managedWindows.length; i++) {
			if (managedWindows[i].win === win) return managedWindows[i];
		}
		return null;
	}

	// --- Start Menu ---
	function initStartMenu() {
		var btn = document.getElementById('start-button');
		var menu = document.getElementById('start-menu');
		if (!btn || !menu) return;

		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			var isOpen = menu.classList.contains('is-open');
			closeAllMenus();
			if (!isOpen) {
				menu.classList.add('is-open');
				btn.classList.add('is-active');
				btn.setAttribute('aria-expanded', 'true');
			}
		});
	}

	// --- Menu Bar Dropdowns ---
	function initMenuBar() {
		var menuItems = document.querySelectorAll('.win95-menu-bar > li');

		menuItems.forEach(function (item) {
			var trigger = item.querySelector('button, a');
			var dropdown = item.querySelector('.win95-dropdown');
			if (!trigger || !dropdown) return;

			trigger.addEventListener('click', function (e) {
				e.stopPropagation();
				var isOpen = dropdown.classList.contains('is-open');
				closeAllMenus();
				if (!isOpen) {
					dropdown.classList.add('is-open');
				}
			});

			item.addEventListener('mouseenter', function () {
				var anyOpen = document.querySelector('.win95-dropdown.is-open');
				if (anyOpen && anyOpen !== dropdown) {
					closeAllMenus();
					dropdown.classList.add('is-open');
				}
			});
		});
	}

	// --- Close all menus ---
	function closeAllMenus() {
		document.querySelectorAll('.win95-dropdown.is-open').forEach(function (el) {
			el.classList.remove('is-open');
		});
		var menu = document.getElementById('start-menu');
		var btn = document.getElementById('start-button');
		if (menu) menu.classList.remove('is-open');
		if (btn) {
			btn.classList.remove('is-active');
			btn.setAttribute('aria-expanded', 'false');
		}
	}

	// --- Detect which resize edges the mouse is near ---
	function getResizeEdges(win, e) {
		var rect = win.getBoundingClientRect();
		var edges = { n: false, s: false, e: false, w: false };
		if (e.clientY - rect.top < RESIZE_HANDLE) edges.n = true;
		if (rect.bottom - e.clientY < RESIZE_HANDLE) edges.s = true;
		if (rect.right - e.clientX < RESIZE_HANDLE) edges.e = true;
		if (e.clientX - rect.left < RESIZE_HANDLE) edges.w = true;
		return edges;
	}

	function edgesToCursor(edges) {
		var v = edges.n ? 'n' : edges.s ? 's' : '';
		var h = edges.w ? 'w' : edges.e ? 'e' : '';
		if (v || h) return (v + h) + '-resize';
		return '';
	}

	// --- Setup drag and resize for a window ---
	function setupWindowInteraction(win) {
		var titleBar = win.querySelector('.win95-title-bar');
		if (!titleBar) return;

		var mode = null;
		var resizeEdges = {};
		var grabX, grabY, startRect;

		// Click to bring to front
		win.addEventListener('mousedown', function () {
			bringToFront(win);
		});

		// Update cursor on mouse move over window edges
		win.addEventListener('mousemove', function (e) {
			if (mode) return;
			if (win.classList.contains('is-maximized')) {
				win.style.cursor = '';
				return;
			}
			var edges = getResizeEdges(win, e);
			var cursor = edgesToCursor(edges);
			win.style.cursor = cursor || '';
		});

		win.addEventListener('mouseleave', function () {
			if (!mode) win.style.cursor = '';
		});

		// Start drag or resize on mousedown
		win.addEventListener('mousedown', function (e) {
			if (e.target.tagName === 'BUTTON') return;
			if (e.target.tagName === 'A') return;
			if (e.target.tagName === 'INPUT') return;
			if (e.target.tagName === 'TEXTAREA') return;
			if (win.classList.contains('is-maximized')) return;

			var edges = getResizeEdges(win, e);
			var isEdge = edges.n || edges.s || edges.e || edges.w;

			var rect = win.getBoundingClientRect();
			startRect = {
				left: win.offsetLeft,
				top: win.offsetTop,
				width: rect.width,
				height: rect.height
			};
			grabX = e.clientX;
			grabY = e.clientY;

			if (isEdge) {
				mode = 'resize';
				resizeEdges = edges;
				win.style.width = rect.width + 'px';
				win.style.height = rect.height + 'px';
			} else if (titleBar.contains(e.target)) {
				mode = 'drag';
				win.style.width = rect.width + 'px';
				win.style.height = rect.height + 'px';
			} else {
				return;
			}

			win.style.transition = 'none';
			document.body.style.userSelect = 'none';
			document.body.style.cursor = edgesToCursor(resizeEdges) || '';
			e.preventDefault();
		});

		function onMouseMove(e) {
			if (!mode) return;

			var dx = e.clientX - grabX;
			var dy = e.clientY - grabY;
			var bounds = getDesktopBounds();

			if (mode === 'drag') {
				var newLeft = startRect.left + dx;
				var newTop = startRect.top + dy;

				// Clamp top: can't go above desktop
				if (newTop < 0) newTop = 0;

				// Clamp bottom: title bar must stay fully above the taskbar
				var maxTop = bounds.height - TITLE_BAR_H;
				if (newTop > maxTop) newTop = maxTop;

				// Clamp left: keep at least 100px of window visible on the right
				var minLeft = -(startRect.width - 100);
				if (newLeft < minLeft) newLeft = minLeft;

				// Clamp right: keep at least 100px of window visible on the left
				var maxLeft = bounds.width - 100;
				if (newLeft > maxLeft) newLeft = maxLeft;

				win.style.left = newLeft + 'px';
				win.style.top = newTop + 'px';
			}

			if (mode === 'resize') {
				var newW = startRect.width;
				var newH = startRect.height;
				var newL = startRect.left;
				var newT = startRect.top;

				if (resizeEdges.e) {
					newW = Math.max(MIN_WIN_W, startRect.width + dx);
				}
				if (resizeEdges.s) {
					newH = Math.max(MIN_WIN_H, startRect.height + dy);
				}
				if (resizeEdges.w) {
					var proposedW = startRect.width - dx;
					if (proposedW >= MIN_WIN_W) {
						newW = proposedW;
						newL = startRect.left + dx;
					}
				}
				if (resizeEdges.n) {
					var proposedH = startRect.height - dy;
					if (proposedH >= MIN_WIN_H) {
						newH = proposedH;
						newT = startRect.top + dy;
					}
					if (newT < 0) {
						newH = newH + newT;
						newT = 0;
					}
				}

				win.style.width = newW + 'px';
				win.style.height = newH + 'px';
				win.style.left = newL + 'px';
				win.style.top = newT + 'px';
			}
		}

		function onMouseUp() {
			if (mode) {
				mode = null;
				resizeEdges = {};
				document.body.style.userSelect = '';
				document.body.style.cursor = '';
			}
		}

		document.addEventListener('mousemove', onMouseMove);
		document.addEventListener('mouseup', onMouseUp);
	}

	// --- Register a window in the taskbar ---
	function registerWindow(win, label, iconSrc) {
		var taskbarWindows = document.getElementById('taskbar-windows');
		if (!taskbarWindows) return;

		// Create taskbar button
		var btn = document.createElement('button');
		btn.className = 'taskbar-window-btn is-active';

		// Add icon to taskbar button if available
		if (iconSrc) {
			var btnIcon = document.createElement('img');
			btnIcon.className = 'win95-icon';
			btnIcon.src = iconSrc;
			btnIcon.width = 16;
			btnIcon.height = 16;
			btnIcon.alt = '';
			btnIcon.draggable = false;
			btn.appendChild(btnIcon);
		}

		var btnText = document.createElement('span');
		btnText.textContent = label.length > 18 ? label.substring(0, 18) + '...' : label;
		btn.appendChild(btnText);

		if (!win.id) win.id = 'win-' + Math.random().toString(36).substr(2, 5);
		btn.dataset.windowId = win.id;
		taskbarWindows.appendChild(btn);

		var mw = {
			win: win,
			btn: btn,
			isMinimized: false,
			label: label,
			iconSrc: iconSrc || ''
		};
		managedWindows.push(mw);
		win._managedRef = mw;

		// Click taskbar button to toggle minimize/restore
		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			closeAllMenus();

			if (mw.isMinimized) {
				// Restore
				win.style.display = 'flex';
				mw.isMinimized = false;
				bringToFront(win);
			} else if (win.classList.contains('is-active')) {
				// Already active — minimize it
				win.style.display = 'none';
				mw.isMinimized = true;
				btn.classList.remove('is-active');
				btn.classList.add('is-minimized');
			} else {
				// Not active — bring to front
				bringToFront(win);
			}
		});

		// Wire up title bar minimize button
		var minBtn = win.querySelector('.win95-btn-minimize');
		if (minBtn) {
			var newMinBtn = minBtn.cloneNode(true);
			minBtn.parentNode.replaceChild(newMinBtn, minBtn);

			newMinBtn.addEventListener('click', function () {
				win.style.display = 'none';
				mw.isMinimized = true;
				btn.classList.remove('is-active');
				btn.classList.add('is-minimized');
			});
		}

		// Wire up close button for spawned windows
		var closeBtn = win.querySelector('.win95-btn-close');
		if (closeBtn && win.dataset.spawned) {
			var newCloseBtn = closeBtn.cloneNode(true);
			closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);

			newCloseBtn.addEventListener('click', function () {
				// Remove from managed windows
				var idx = managedWindows.indexOf(mw);
				if (idx > -1) managedWindows.splice(idx, 1);
				btn.remove();
				win.remove();
			});
		}

		return mw;
	}

	// --- Spawn a new window from a URL (desktop icon click, etc.) ---
	function spawnWindow(url, title, iconSrc) {
		// Check max windows
		if (managedWindows.length >= MAX_WINDOWS) {
			// Find a spawned window to reuse
			var reusable = null;
			for (var i = managedWindows.length - 1; i >= 0; i--) {
				if (managedWindows[i].win.dataset.spawned) {
					reusable = managedWindows[i];
					break;
				}
			}
			if (reusable) {
				// Close the oldest spawned window
				reusable.btn.remove();
				reusable.win.remove();
				managedWindows.splice(managedWindows.indexOf(reusable), 1);
			}
		}

		var desktop = document.getElementById('desktop');
		if (!desktop) return;

		// Build the window element
		var win = document.createElement('div');
		win.className = 'win95-window main-window is-active';
		win.dataset.spawned = 'true';

		var windowTitle = title + ' \u2013 Notepad';

		win.innerHTML =
			'<div class="win95-title-bar">' +
				(iconSrc ? '<img class="win95-icon" src="' + iconSrc + '" width="16" height="16" alt="" style="margin-right:3px;flex-shrink:0" draggable="false">' : '') +
				'<span class="win95-title-bar-text">' + escapeHtml(windowTitle) + '</span>' +
				'<div class="win95-title-bar-controls">' +
					'<button class="win95-btn-minimize" aria-label="Minimize"></button>' +
					'<button class="win95-btn-maximize" aria-label="Maximize"></button>' +
					'<button class="win95-btn-close" aria-label="Close"></button>' +
				'</div>' +
			'</div>' +
			'<div class="win95-window-body">' +
				'<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#808080;">Loading...</div>' +
			'</div>' +
			'<ul class="win95-status-bar">' +
				'<li class="win95-status-bar__field">Loading...</li>' +
			'</ul>';

		desktop.appendChild(win);

		// Position with cascade
		var spawnIndex = managedWindows.length;
		positionWindow(win, spawnIndex);

		// Setup interaction
		setupWindowInteraction(win);
		var mw = registerWindow(win, windowTitle, iconSrc);
		bringToFront(win);

		// Setup maximize
		var maxBtn = win.querySelector('.win95-btn-maximize');
		if (maxBtn) {
			maxBtn.addEventListener('click', function () {
				if (win.classList.contains('is-maximized')) {
					win.classList.remove('is-maximized');
					win.style.cssText = win.dataset.prevStyle || '';
				} else {
					win.dataset.prevStyle = win.style.cssText;
					win.classList.add('is-maximized');
					win.style.position = 'absolute';
					win.style.top = '0';
					win.style.left = '0';
					win.style.width = '100%';
					win.style.height = '100%';
					win.style.maxWidth = '100%';
					win.style.zIndex = highestZ;
				}
			});
		}

		// Double-click title bar to maximize
		var titleBar = win.querySelector('.win95-title-bar');
		if (titleBar) {
			titleBar.addEventListener('dblclick', function (e) {
				if (e.target.tagName === 'BUTTON') return;
				if (maxBtn) maxBtn.click();
			});
		}

		// Fetch the page content via AJAX (use local path to avoid CORS)
		fetch(toLocalPath(url), { credentials: 'same-origin' })
			.then(function (response) {
				if (!response.ok) throw new Error('HTTP ' + response.status);
				return response.text();
			})
			.then(function (html) {
				var parser = new DOMParser();
				var doc = parser.parseFromString(html, 'text/html');

				// Extract the window body content
				var sourceBody = doc.querySelector('.win95-window-body');
				var targetBody = win.querySelector('.win95-window-body');

				if (sourceBody && targetBody) {
					targetBody.innerHTML = sourceBody.innerHTML;
				} else {
					// Fallback: try to find main content
					var mainContent = doc.querySelector('article') || doc.querySelector('.post-list') || doc.querySelector('main');
					if (mainContent && targetBody) {
						targetBody.innerHTML = mainContent.innerHTML;
					}
				}

				// Re-execute inline scripts (innerHTML doesn't run them)
				if (targetBody) {
					var scripts = targetBody.querySelectorAll('script');
					scripts.forEach(function (oldScript) {
						var newScript = document.createElement('script');
						if (oldScript.src) {
							newScript.src = oldScript.src;
						} else {
							newScript.textContent = oldScript.textContent;
						}
						oldScript.parentNode.replaceChild(newScript, oldScript);
					});
				}

				// Update status bar
				var statusField = win.querySelector('.win95-status-bar__field');
				if (statusField) statusField.textContent = 'Done';

				// Intercept links inside the spawned window to open in same window or spawn new
				interceptLinks(win);
			})
			.catch(function () {
				var targetBody = win.querySelector('.win95-window-body');
				if (targetBody) {
					targetBody.innerHTML = '<div style="padding:16px;color:#808080;">Could not load page.</div>';
				}
			});

		return win;
	}

	function escapeHtml(str) {
		var div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	// --- Convert any URL to a same-origin path to avoid CORS issues ---
	// Handles WordPress URL mismatch (e.g., WP configured with temp URL but accessed via real domain)
	function toLocalPath(url) {
		try {
			var u = new URL(url, window.location.href);
			var path = u.pathname + u.search + u.hash;

			// If WordPress home URL differs from current origin, strip the WP base path
			if (typeof win95Data !== 'undefined' && win95Data.homeUrl) {
				var wpBase = new URL(win95Data.homeUrl);
				var wpPath = wpBase.pathname.replace(/\/$/, ''); // e.g., "/website_c78c712e/website_c78c712e"

				// If the URL contains the WP base path, strip it to get the real relative path
				if (wpPath && wpPath !== '/' && wpPath !== '' && path.indexOf(wpPath) === 0) {
					path = path.substring(wpPath.length) || '/';
				}
			}

			return path;
		} catch (e) {
			return url;
		}
	}

	// --- Intercept links inside spawned windows ---
	function interceptLinks(win) {
		win.querySelectorAll('a[href]').forEach(function (link) {
			// Only intercept internal links (skip external and anchor-only links)
			try {
				var linkUrl = new URL(link.href, window.location.href);
				if (linkUrl.origin !== window.location.origin && linkUrl.hostname !== link.hostname) return;
			} catch (e) { return; }

			link.addEventListener('click', function (e) {
				e.preventDefault();
				var body = win.querySelector('.win95-window-body');
				if (body) {
					body.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#808080;">Loading...</div>';
				}

				var statusField = win.querySelector('.win95-status-bar__field');
				if (statusField) statusField.textContent = 'Loading...';

				fetch(toLocalPath(link.href), { credentials: 'same-origin' })
					.then(function (r) {
						if (!r.ok) throw new Error('HTTP ' + r.status);
						return r.text();
					})
					.then(function (html) {
						var parser = new DOMParser();
						var doc = parser.parseFromString(html, 'text/html');
						var sourceBody = doc.querySelector('.win95-window-body');

						if (sourceBody && body) {
							body.innerHTML = sourceBody.innerHTML;
						} else {
							var mainContent = doc.querySelector('article') || doc.querySelector('.post-list');
							if (mainContent && body) {
								body.innerHTML = mainContent.innerHTML;
							}
						}

						// Re-execute inline scripts
						if (body) {
							var scripts = body.querySelectorAll('script');
							scripts.forEach(function (oldScript) {
								var newScript = document.createElement('script');
								if (oldScript.src) {
									newScript.src = oldScript.src;
								} else {
									newScript.textContent = oldScript.textContent;
								}
								oldScript.parentNode.replaceChild(newScript, oldScript);
							});
						}

						// Update title bar
						var pageTitle = doc.querySelector('title');
						var titleText = win.querySelector('.win95-title-bar-text');
						if (pageTitle && titleText) {
							titleText.textContent = pageTitle.textContent;
						}

						if (statusField) statusField.textContent = 'Done';
						interceptLinks(win);
					})
					.catch(function () {
						if (body) body.innerHTML = '<div style="padding:16px;color:#808080;">Could not load page.</div>';
					});
			});
		});
	}

	// --- Spawn a PDF viewer window (Acrobat Reader style) ---
	function spawnPdfViewer(pdfUrl, title) {
		// Check max windows
		if (managedWindows.length >= MAX_WINDOWS) {
			var reusable = null;
			for (var i = managedWindows.length - 1; i >= 0; i--) {
				if (managedWindows[i].win.dataset.spawned) {
					reusable = managedWindows[i];
					break;
				}
			}
			if (reusable) {
				reusable.btn.remove();
				reusable.win.remove();
				managedWindows.splice(managedWindows.indexOf(reusable), 1);
			}
		}

		var desktop = document.getElementById('desktop');
		if (!desktop) return;

		// Clean up title — strip .pdf extension
		var displayTitle = title.replace(/\.pdf$/i, '');
		var windowTitle = displayTitle + ' \u2013 Acrobat Reader';

		var win = document.createElement('div');
		win.className = 'win95-window main-window is-active pdf-viewer-window';
		win.dataset.spawned = 'true';
		win.dataset.pageUrl = pdfUrl;

		win.innerHTML =
			'<div class="win95-title-bar">' +
				'<span class="win95-title-bar-text" style="display:flex;align-items:center;gap:3px;">' +
					'<svg width="16" height="16" viewBox="0 0 16 16" style="flex-shrink:0"><rect x="1" y="1" width="14" height="14" rx="1" fill="#cc0000"/><text x="8" y="12" font-size="8" font-weight="bold" fill="#fff" text-anchor="middle" font-family="Arial,sans-serif">PDF</text></svg>' +
					escapeHtml(windowTitle) +
				'</span>' +
				'<div class="win95-title-bar-controls">' +
					'<button class="win95-btn-minimize" aria-label="Minimize"></button>' +
					'<button class="win95-btn-maximize" aria-label="Maximize"></button>' +
					'<button class="win95-btn-close" aria-label="Close"></button>' +
				'</div>' +
			'</div>' +
			// Acrobat-style toolbar
			'<div class="pdf-toolbar">' +
				'<div class="pdf-toolbar__group">' +
					'<button class="pdf-toolbar__btn" title="Open" disabled>&#128194;</button>' +
					'<button class="pdf-toolbar__btn" title="Print" onclick="window.open(\'' + escapeHtml(pdfUrl) + '\')">&#128424;</button>' +
					'<span class="pdf-toolbar__separator"></span>' +
					'<button class="pdf-toolbar__btn pdf-toolbar__nav" title="Previous Page" disabled>&#9664;</button>' +
					'<span class="pdf-toolbar__page-display">Page 1</span>' +
					'<button class="pdf-toolbar__btn pdf-toolbar__nav" title="Next Page" disabled>&#9654;</button>' +
					'<span class="pdf-toolbar__separator"></span>' +
					'<button class="pdf-toolbar__btn" title="Zoom Out" disabled>&#8722;</button>' +
					'<span class="pdf-toolbar__zoom-display">100%</span>' +
					'<button class="pdf-toolbar__btn" title="Zoom In" disabled>&#43;</button>' +
				'</div>' +
				'<div class="pdf-toolbar__group">' +
					'<a href="' + escapeHtml(pdfUrl) + '" class="pdf-toolbar__btn" title="Download" download style="text-decoration:none">&#128190;</a>' +
				'</div>' +
			'</div>' +
			'<div class="win95-window-body pdf-viewer-body">' +
				'<embed src="' + escapeHtml(pdfUrl) + '" type="application/pdf" width="100%" height="100%" style="border:none;display:block;">' +
			'</div>' +
			'<ul class="win95-status-bar">' +
				'<li class="win95-status-bar__field">' + escapeHtml(displayTitle) + '</li>' +
			'</ul>';

		desktop.appendChild(win);

		// Position with cascade
		positionWindow(win, managedWindows.length);

		// Setup interaction
		setupWindowInteraction(win);
		var mw = registerWindow(win, windowTitle, '');
		bringToFront(win);

		// Setup maximize
		var maxBtn = win.querySelector('.win95-btn-maximize');
		if (maxBtn) {
			maxBtn.addEventListener('click', function () {
				if (win.classList.contains('is-maximized')) {
					win.classList.remove('is-maximized');
					win.style.cssText = win.dataset.prevStyle || '';
				} else {
					win.dataset.prevStyle = win.style.cssText;
					win.classList.add('is-maximized');
					win.style.position = 'absolute';
					win.style.top = '0';
					win.style.left = '0';
					win.style.width = '100%';
					win.style.height = '100%';
					win.style.maxWidth = '100%';
					win.style.zIndex = highestZ;
				}
			});
		}

		// Double-click title bar to maximize
		var titleBar = win.querySelector('.win95-title-bar');
		if (titleBar) {
			titleBar.addEventListener('dblclick', function (e) {
				if (e.target.tagName === 'BUTTON') return;
				if (maxBtn) maxBtn.click();
			});
		}

		return win;
	}

	// --- Check if a URL points to a PDF ---
	function isPdfUrl(url) {
		try {
			var u = new URL(url, window.location.href);
			return /\.pdf($|\?|#)/i.test(u.pathname);
		} catch (e) {
			return /\.pdf($|\?|#)/i.test(url);
		}
	}

	// --- Intercept PDF links across the entire page ---
	function initPdfLinks() {
		document.addEventListener('click', function (e) {
			var link = e.target.closest('a[href]');
			if (!link) return;
			if (!isPdfUrl(link.href)) return;

			// Don't intercept if modifier keys are held (let user force-download etc.)
			if (e.ctrlKey || e.metaKey || e.shiftKey) return;

			e.preventDefault();

			var pdfUrl = link.href;
			var title = link.textContent.trim() || 'Document';

			// Check if already open
			for (var i = 0; i < managedWindows.length; i++) {
				var mw = managedWindows[i];
				if (mw.win.dataset.pageUrl === pdfUrl) {
					if (mw.isMinimized) {
						mw.win.style.display = 'flex';
						mw.isMinimized = false;
					}
					bringToFront(mw.win);
					return;
				}
			}

			spawnPdfViewer(pdfUrl, title);
		});
	}

	// --- Desktop Icon Click Handler ---
	function initDesktopIcons() {
		document.querySelectorAll('.desktop-icon').forEach(function (icon) {
			// Selection highlight on click
			icon.addEventListener('mousedown', function () {
				document.querySelectorAll('.desktop-icon.is-selected').forEach(function (i) {
					i.classList.remove('is-selected');
				});
				icon.classList.add('is-selected');
			});

			icon.addEventListener('click', function (e) {
				e.preventDefault();

				var url = icon.getAttribute('href');
				var label = icon.querySelector('.desktop-icon__label');
				var title = label ? label.textContent.trim() : 'Document';

				// Get icon image src for taskbar
				var iconImg = icon.querySelector('.desktop-icon__image');
				var iconSrc = iconImg ? iconImg.src : '';

				// Check if a window is already open for this URL
				for (var i = 0; i < managedWindows.length; i++) {
					var mw = managedWindows[i];
					if (mw.win.dataset.pageUrl === url) {
						// Already open — restore and bring to front
						if (mw.isMinimized) {
							mw.win.style.display = 'flex';
							mw.isMinimized = false;
						}
						bringToFront(mw.win);
						return;
					}
				}

				var win = spawnWindow(url, title, iconSrc);
				if (win) win.dataset.pageUrl = url;
			});
		});
	}

	// --- Init main window position ---
	function initMainWindow() {
		var mainWin = document.querySelector('.win95-window.main-window');
		if (!mainWin) return;

		// Position the main window
		positionWindow(mainWin, 0);

		// Remove the CSS defaults that conflict
		mainWin.style.maxWidth = 'none';
	}

	// --- Window Maximize Controls ---
	function initWindowControls() {
		document.querySelectorAll('.win95-window.main-window:not([data-spawned])').forEach(function (win) {
			var maxBtn = win.querySelector('.win95-btn-maximize');
			if (!maxBtn) return;

			maxBtn.addEventListener('click', function () {
				if (win.classList.contains('is-maximized')) {
					win.classList.remove('is-maximized');
					win.style.cssText = win.dataset.prevStyle || '';
				} else {
					win.dataset.prevStyle = win.style.cssText;
					win.classList.add('is-maximized');
					win.style.position = 'absolute';
					win.style.top = '0';
					win.style.left = '0';
					win.style.width = '100%';
					win.style.height = '100%';
					win.style.maxWidth = '100%';
					win.style.zIndex = highestZ;
				}
			});
		});
	}

	// --- Title bar double-click to maximize ---
	function initTitleBarDblClick() {
		document.querySelectorAll('.win95-window.main-window:not([data-spawned]) .win95-title-bar').forEach(function (bar) {
			bar.addEventListener('dblclick', function (e) {
				if (e.target.tagName === 'BUTTON') return;
				var maxBtn = bar.querySelector('.win95-btn-maximize');
				if (maxBtn) maxBtn.click();
			});
		});
	}

	// --- Click outside to close menus + deselect desktop icons ---
	document.addEventListener('click', function (e) {
		closeAllMenus();
		// Deselect desktop icons if clicking on desktop background
		if (e.target.id === 'desktop' || e.target.classList.contains('desktop-icons-grid')) {
			document.querySelectorAll('.desktop-icon.is-selected').forEach(function (i) {
				i.classList.remove('is-selected');
			});
		}
	});

	// --- Escape to close menus ---
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			closeAllMenus();
		}
	});

	// --- Initialize everything on DOM ready ---
	document.addEventListener('DOMContentLoaded', function () {
		updateClock();
		setInterval(updateClock, 30000);
		initStartMenu();
		initMenuBar();

		// Position main window first
		initMainWindow();

		// Setup interaction for existing windows
		document.querySelectorAll('.win95-window.main-window').forEach(function (win) {
			setupWindowInteraction(win);
		});

		// Setup taskbar
		var taskbarWindows = document.getElementById('taskbar-windows');
		if (taskbarWindows) taskbarWindows.innerHTML = '';

		// Register existing windows
		document.querySelectorAll('.win95-window.main-window').forEach(function (win) {
			var titleText = win.querySelector('.win95-title-bar-text');
			var label = titleText ? titleText.textContent : 'Window';
			var titleIcon = win.querySelector('.win95-title-bar .win95-icon');
			var iconSrc = (titleIcon && titleIcon.src) ? titleIcon.src : '';
			registerWindow(win, label, iconSrc);
		});

		initWindowControls();
		initTitleBarDblClick();
		initDesktopIcons();
		initPdfLinks();
		initSocialWindow();
	});

	// --- Social Window ---
	function initSocialWindow() {
		var socialWin = document.getElementById('social-window');
		if (!socialWin) return;

		// Position in bottom-right, above taskbar
		var bounds = getDesktopBounds();

		// Let browser render it, then measure and position with left/top
		socialWin.style.position = 'absolute';
		socialWin.style.visibility = 'hidden';
		socialWin.style.display = 'flex';

		// Force layout so we get accurate measurements
		var socialW = socialWin.offsetWidth || 200;
		var socialH = socialWin.offsetHeight || 80;

		// Position with comfortable margins: 16px from right edge, 8px above taskbar
		var socialLeft = bounds.width - socialW - 16;
		var socialTop = bounds.height - socialH - 8;

		// Safety: don't let it go below work area
		if (socialTop + socialH > bounds.height) {
			socialTop = bounds.height - socialH;
		}

		socialWin.style.left = Math.max(0, socialLeft) + 'px';
		socialWin.style.top = Math.max(0, socialTop) + 'px';
		socialWin.style.visibility = '';
		socialWin.style.right = '';
		socialWin.style.bottom = '';

		// Setup interaction
		setupWindowInteraction(socialWin);

		// Register in taskbar
		var iconImg = socialWin.querySelector('.win95-title-bar .win95-icon');
		var iconSrc = (iconImg && iconImg.src) ? iconImg.src : '';
		var socialMW = registerWindow(socialWin, 'Social', iconSrc);
		bringToFront(document.getElementById('main-window') || socialWin);

		// Wire close button to hide (not remove) the social window
		var closeBtn = socialWin.querySelector('.win95-btn-close');
		if (closeBtn) {
			var newCloseBtn = closeBtn.cloneNode(true);
			closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);

			newCloseBtn.addEventListener('click', function () {
				socialWin.style.display = 'none';
				if (socialMW) {
					socialMW.isMinimized = true;
					if (socialMW.btn) {
						socialMW.btn.classList.remove('is-active');
						socialMW.btn.classList.add('is-minimized');
					}
				}
			});
		}

		// Quick Launch toggle — always restores the social window
		var quickLaunchBtn = document.getElementById('social-quick-launch');
		if (quickLaunchBtn) {
			quickLaunchBtn.addEventListener('click', function (e) {
				e.preventDefault();
				if (!socialMW) return;

				if (socialMW.isMinimized || socialWin.style.display === 'none') {
					socialWin.style.display = 'flex';
					socialMW.isMinimized = false;
					if (socialMW.btn) {
						socialMW.btn.classList.remove('is-minimized');
					}
					bringToFront(socialWin);
				} else {
					bringToFront(socialWin);
				}
			});
		}
	}
})();
