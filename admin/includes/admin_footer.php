            </main>
        </div><!-- /.admin-main -->
    </div><!-- /.admin-layout -->

    <script>
    // Client-side enhancements for Admin forms and tables
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const forms = document.querySelectorAll('form:not(.no-validate)');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const requiredInputs = form.querySelectorAll('[required]');
                let hasError = false;
                let firstInvalid = null;

                requiredInputs.forEach(function(input) {
                    if (input.type === 'file') {
                        if (!input.files || input.files.length === 0) {
                            if (!input.disabled && !input.readOnly) {
                                hasError = true;
                                input.style.borderColor = '#ef4444';
                                if (!firstInvalid) firstInvalid = input;
                            }
                        } else {
                            input.style.borderColor = '';
                        }
                    } else if (input.value.trim() === '') {
                        hasError = true;
                        input.style.borderColor = '#ef4444';
                        if (!firstInvalid) firstInvalid = input;
                    } else {
                        input.style.borderColor = '';
                    }

                    // Email format check
                    if (input.type === 'email' && input.value.trim() !== '') {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(input.value.trim())) {
                            hasError = true;
                            input.style.borderColor = '#ef4444';
                            if (!firstInvalid) firstInvalid = input;
                        }
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });

        // Clear error style on input
        document.querySelectorAll('input, select, textarea').forEach(function(el) {
            el.addEventListener('input', function() {
                if (this.style.borderColor === 'rgb(239, 68, 68)' || this.style.borderColor === '#ef4444') {
                    this.style.borderColor = '';
                }
            });
        });
    });
    </script>
</body>
</html>
