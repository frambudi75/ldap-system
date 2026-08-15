<?php
/**
 * Layout: Footer & Background Poller Scripts
 */
?>
    </div><!-- Close flex-1 from header -->

    <!-- Dynamic Status Poller Script -->
    <script>
        // Poll daemon status every 8 seconds
        function updateDaemonStatus() {
            fetch('<?= BASE_URL ?>/api/status.php')
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('daemon-status-badge');
                    if (badge) {
                        if (data.online) {
                            badge.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30';
                            badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Active';
                        } else {
                            badge.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/30';
                            badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Offline';
                        }
                    }
                })
                .catch(err => console.debug('Status check error:', err));
        }
        setInterval(updateDaemonStatus, 8000);
    </script>
</body>
</html>
