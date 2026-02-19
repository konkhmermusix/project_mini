 </div>
 </div>
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
 <script>
     const sidebar = document.getElementById('sidebar');
     const toggleBtn = document.getElementById('sidebarToggle');
     const closeBtn = document.getElementById('closeSidebar');

     toggleBtn.addEventListener('click', () => {
         sidebar.classList.add('show');
     });

     closeBtn.addEventListener('click', () => {
         sidebar.classList.remove('show');
     });
 </script>
 </body>

 </html>