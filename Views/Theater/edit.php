<div class="container-fluid white-background section-container py-4">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 col-md-9 form-container mx-auto">
        <h1>Editar cine</h1>
        <hr>
        <?php foreach($responses as $response) { ?>
          <?php if($response->isSuccess()) { ?>
            <div class="alert alert-success" role="alert">
              <?php echo $response->getMessage(); ?>
            </div>
          <?php } else { ?>
            <div class="alert alert-danger" role="alert">
              <?php echo $response->getMessage(); ?>
            </div>
          <?php } ?>
        <?php } ?>
        <form action="<?php echo FRONT_ROOT ?>/Theater/Edit" method="POST">
          <input type="hidden" name="id" value="<?php echo $theater->getId(); ?>">
          <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" name="name" class="form-control" id="name" value="<?php echo $theater->getName(); ?>" placeholder="Ingrese el nombre" maxlength="100" required>
          </div>
          <div class="form-group">
            <label for="address">Dirección</label>
            <input type="text" name="address" class="form-control" id="address" value="<?php echo $theater->getAddress(); ?>" placeholder="Ingrese la dirección" maxlength="150" required>
          </div>
          <button type="submit" class="btn btn-dark w-100 mt-2">Confirmar</button>
        </form>
      </div>
    </div>
  </div>
</div>