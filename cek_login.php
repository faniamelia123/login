<?php 
session_start();

// koneksi database
include 'koneksi.php';

// menangkap data dari form login
$username = $_POST['username'];
$password = $_POST['password'];

// cek data di database
$login = mysqli_query($koneksi,"SELECT * FROM user WHERE username='$username' AND password='$password'");
$cek = mysqli_num_rows($login);

if($cek > 0){

    $data = mysqli_fetch_assoc($login);

    // jika admin
    if($data['level'] == "admin"){

        $_SESSION['username'] = $data['username'];
        $_SESSION['level'] = "admin";
        header("location:halaman_admin.php");

    // jika bendahara
    } else if($data['level'] == "bendahara"){

        $_SESSION['username'] = $data['username'];
        $_SESSION['level'] = "bendahara";
        header("location:halaman_bendahara.php");

    // jika pengguna
    } else if($data['level'] == "pengguna"){

        $_SESSION['username'] = $data['username'];
        $_SESSION['level'] = "pengguna";
        header("location:halaman_pengguna.php");

    } else {
        header("location:index.php?pesan=gagal");
    }

} else {
    header("location:index.php?pesan=gagal");
}
?>
