 </div>
 </div>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
 <script>
     const sidebar = document.getElementById('sidebar');
     const toggleBtn = document.getElementById('sidebarToggle');
     const closeBtn = document.getElementById('closeSidebar');

     // Open sidebar (mobile)
     toggleBtn.addEventListener('click', () => {
         sidebar.classList.add('show');
     });

     // Close sidebar (mobile)
     closeBtn.addEventListener('click', () => {
         sidebar.classList.remove('show');
     });
 </script>
 </body>

 </html>