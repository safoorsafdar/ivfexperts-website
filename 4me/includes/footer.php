        </main>
        
        <!-- Footer Wrapper closing -->
    </div> <!-- End Main Content Wrapper -->

    <script>
    // ── Global Keyboard Shortcuts ──────────────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        // Don't trigger when user is typing in a field
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT' || e.target.isContentEditable) return;

        var isMod = e.metaKey || e.ctrlKey;

        // ⌘K / Ctrl+K — Jump to Patient Hub (search)
        if (isMod && e.key === 'k') {
            e.preventDefault();
            window.location.href = 'patients.php';
        }
        // ⌘N / Ctrl+N — New Patient
        if (isMod && e.key === 'n') {
            e.preventDefault();
            window.location.href = 'patients_add.php';
        }
        // ⌘P / Ctrl+P — New Prescription (override browser print on admin pages)
        if (isMod && e.key === 'p' && !document.querySelector('.rx-page')) {
            e.preventDefault();
            window.location.href = 'prescriptions_add.php';
        }
    });
    </script>
</body>
</html>