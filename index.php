<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

    <title>Report AI BOT!</title>
  </head>
  <body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Navbar</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Dropdown
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
  </div>
</nav>
<?php
$pair = json_decode(file_get_contents(__DIR__."/symbol.json"));
$balance = json_decode(file_get_contents(__DIR__."/balance.json"));
$wait = glob(__DIR__."/orders/*.json");
$report = glob(__DIR__."/report/*.json");
?>
    <div class="container">
        <h1>Report Trader</h1>
        <div class="row">
          <div class="col">
            <div class="card">
              <div class="card-header">Start : </div>
              <div class="card-body">
                  <h4>BTC</h4>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card">
              <div class="card-header">Customs : </div>
              <div class="card-body">
                  <h4>BTC</h4>
              </div>
            </div>
          </div>


          <div class="col">
            <div class="card">
              <div class="card-header">Coin Trade : </div>
              <div class="card-body">
                  <h4>BTC</h4>
              </div>
            </div>
          </div>


          <div class="col">
            <div class="card">
              <div class="card-header">Start : </div>
              <div class="card-body">
                  <h4>BTC</h4>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="card">
              <div class="card-header">Start : </div>
              <div class="card-body">
                  <h4>BTC</h4>
              </div>
            </div>
          </div>
        </div>
        <h1>Wait Order</h1>
        <table class="table table-hover">
            <thead>
              <th>Pair</th>
              <th>Buy At</th>
              <th>Prices</th>
              <th>Amount</th>
              <th>Status</th>
            </thead>
            <tbody>
              <?php foreach($wait as $key => $value){ ?>
              <tr>
                <td><?php echo str_replace('.json','',basename($value));?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <?php } ?>
            </tbody>
        </table>
        <h1>Trips</h1>
        <table class="table table-hover">
            <thead>
              <th>Pair</th>
              <th>Buy Price</th>
              <th>Sell Price</th>
              <th>Amount</th>
              <th>Commiss</th>
              <th>Finish At</th>
              
            </thead>
        </table>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
  </body>
</html>