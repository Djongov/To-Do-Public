</main>
<footer>
<p>Logged in as: <?php echo (isset($_SESSION['loggedin'])) ? $_SESSION['username'] : 'Not Logged in'?></p>
<p>
<?=(isset($_SESSION['username'])) ? '<a href="/logout">Logout</a>  | <a href="/lists">All Lists</a>' : null;?>
<?=(isset($_SESSION['username']) && checkAdmin($_SESSION['username'], $link)) ? '
| <a href="/admin">Admin</a>' : null;?></p>
</footer>
</body>
</html>