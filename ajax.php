<?php
include($_SERVER['DOCUMENT_ROOT'] . "/php/sessions.php");
include($_SERVER['DOCUMENT_ROOT'] . "/php/includes.php");
$mensaje = "";
$tabla     = "co_ws_trazabilidad";
$condicion = "WHERE id_usuario_ws=" . $_SESSION['s_id_usuario'] . "";

$array_id_accesos = array(5, 7, 8, 22, 32, 42, 44, 45, 48, 50, 60, 70, 74);

$desde = date('Y-m-d');

if (isset($_GET['registros'])) {
  $items = $_GET['registros'];
} else {
  $items = 100;
}

$order     = 'created_at';

$trazabilidad = paginacion_filtro($tabla, $condicion, $items, "id_ws_trazabilidad", true);
$total = $trazabilidad['pagina'];

$registros_trazabilidad = buscar('id_ws_trazabilidad,prefix,folio,status_tracking,created_at', $tabla, $trazabilidad['query_condicion']);

if ($registros_trazabilidad->num_rows == 0) {
  $mensaje = "No hay registros que mostrar";
}

//SELECT prefix from co_ws_trazabilidad GROUP BY prefix;

$campos    = "prefix";
$tabla     = "co_ws_trazabilidad";
$condicion = "WHERE id_usuario_ws=" . $_SESSION['s_id_usuario'] . " GROUP BY prefix";

$prefijos = buscar($campos, $tabla, $condicion);

// SELECT status_tracking from co_ws_trazabilidad GROUP BY status_tracking;
$campo_status     = "status_tracking";
$tabla_status     = "co_ws_trazabilidad";
$condicion_status = "WHERE id_usuario_ws=" . $_SESSION['s_id_usuario'] . " GROUP BY status_tracking";
$status = buscar($campo_status, $tabla_status, $condicion_status);

$siteURL = siteURL();
if (empty($se_usuario) && empty($se_password)) :
?>
  <script type="text/javascript" language="javascript">
    alert("Para acceder al administrador es necesario introducir su Usuario y Contrasena");
    location.href = "../login/";
  </script>
<?php
endif;
if (empty($seccion)) :
  $seccion = "Trazabilidad_nomina";
endif;
if (empty($tipo_seccion)) :
  $tipo_seccion = "trazabilidad_nomina";
endif;
require_once($_SERVER['DOCUMENT_ROOT'] . "/libraries/Page.php");
$page = new Page;
$page->setTitle($seccion);
$page->setTipo($tipo_seccion);
/*$page->addCSS($siteURL."css/sweetalert.css");*/
$page->addCSS($siteURL . "css-demo/sweetalert.css");
$page->startBody();
$page->addStyles('');
?>
<style>
  .modal-header,
  h4,
  .close {
    background-color: #5cb85c;
    color: white !important;
    text-align: center;
    font-size: 22px;
  }

  .modal-footer {
    background-color: #f9f9f9;
  }

  .modal-body {
    font-size: 12px;
  }

  .pagination>li:first-child>a,
  .pagination>li:first-child>span {
    position: inherit !important;
  }

  .content-pagination {
    /*margin: 0px;*/
    margin-top: -25px;
    margin-bottom: 50px;
    margin-left: 220px;
    display: inherit;
  }

  .pagination>.active>a,
  .pagination>.active>span,
  .pagination>.active>a:hover,
  .pagination>.active>span:hover,
  .pagination>.active>a:focus,
  .pagination>.active>span:focus,
  .pagination>li>a:hover,
  .pagination>li>a:focus,
  .page-links a:hover {
    background-color: #337ab7 !important;
    border-color: #0063b5 !important;
    color: white;
    position: inherit !important;
  }

  .box-search {
    padding: 28px;
    border-radius: 10px;
    border: 1px solid;
    background: #1C3E87;
    -webkit-box-shadow: 10px 10px 5px 0px rgba(0, 0, 0, 0.75);
    -moz-box-shadow: 10px 10px 5px 0px rgba(0, 0, 0, 0.75);
    box-shadow: 10px 10px 5px 0px rgba(0, 0, 0, 0.75);
  }

  .box-search h2 {
    color: #fff !important;
    font-size: 25px;
    font-weight: bold;
    text-transform: uppercase;
  }

  .box-search label {
    color: #fff;
  }

  .search-btn-content {
    margin-top: 25px;
  }

  .search-btn-content button {
    width: 250px;
    background-color: #47a447;
    color: #fff;
    border: 1px solid #47a447;
    font-size: 18px;
    font-weight: 500;
    text-transform: uppercase
  }

  .btn-detalle {
    font-size: 12px;
    text-transform: uppercase;
    font-weight: bold;
  }

  .modal-header h4 {
    text-transform: uppercase;
    font-weight: 400;
  }
</style>
<!-- Modal vista previa -->
<div class="modal fade" id="modal_trazabilidad" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4>Detalle de trazabilidad</h4>
      </div>
      <div class="modal-body" id="modal_content">

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger btn-default pull-right" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<div class="container">
  <div id="bridge">
    <div id="fb-root"></div>
    <div class="container">
      <div class="page-header ">
        <h1>Trazabilidad de Nomina</h1>
      </div>
      <div class="row">
        <div class="col-sm-12" id="contenido">
          <form name="form_busqueda" id="form_busqueda" action="./" method="get">
            <fieldset class="box-search">
              <h2>Parámetros de búsqueda</h2>
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label for="">Registros</label>
                    <select class="form-control" name="registros" id="registros">
                      <?php
                      $options_registros = array("10" => 10, "30" => 30, "50" => 50, "100" => 100);

                      foreach ($options_registros as $registro) {
                        if (isset($_GET['registros'])) {
                          if ($_GET['registros'] == $registro) {
                            echo "<option value='$registro' selected>$registro</option>";
                          } else {
                            echo "<option value='$registro'>$registro</option>";
                          }
                        } else {
                          echo "<option value='$registro'>$registro</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                  <label for="">Desde</label>
                  <input type="date" class="form-control" name="desde" id="desde" value="<?php if (isset($_GET['desde'])) {
                                                                                            echo $_GET['desde'];
                                                                                          } else {
                                                                                            echo date('Y-m') . '-01';
                                                                                          } ?>" size="15" />
                </div>
                <div class="col-md-5">
                  <label for="">Hasta</label>
                  <input type="date" class="form-control" name="hasta" id="hasta" value="<?php if (isset($_GET['desde'])) {
                                                                                            echo $_GET['hasta'];
                                                                                          } else {
                                                                                            echo date('Y-m-d');
                                                                                          } ?>" size="15" />
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <label for="">Folio</label>
                  <input type="text" class="form-control" name="folio" id="folio" size="15" value="<?php if (isset($_GET['folio'])) {
                                                                                                      echo $_GET['folio'];
                                                                                                    } else {
                                                                                                      echo "";
                                                                                                    } ?>" />
                </div>
                <div class="col-md-4">
                  <label for="">Prefijo</label>
                  <select class="form-control" name="prefijo" id="prefijo">
                    <option value="">Todos</option>
                    <?php
                    while ($prefijo = $prefijos->fetch_array()) {
                      if (isset($_GET['prefijo'])) {
                        if ($_GET['prefijo'] == $prefijo['prefix']) {
                          echo "<option value='" . $prefijo['prefix'] . "' selected>" . $prefijo['prefix'] . "</option>";
                        } else {
                          echo "<option value='" . $prefijo['prefix'] . "'>" . $prefijo['prefix'] . "</option>";
                        }
                      } else {
                        echo "<option value='" . $prefijo['prefix'] . "'>" . $prefijo['prefix'] . "</option>";
                      }
                    }
                    ?>

                  </select>
                </div>
                <div class="col-md-5">
                  <label for="">Status</label>
                  <select class="form-control" name="status" id="status">
                    <option value="">Todos</option>
                    <?php
                    while ($estado = $status->fetch_array()) {
                      if (isset($_GET['status'])) {
                        if ($_GET['status'] == $estado['status_tracking']) {
                          echo "<option value='" . $estado['status_tracking'] . "' selected>" . $estado['status_tracking'] . "</option>";
                        } else {
                          echo "<option value='" . $estado['status_tracking'] . "'>" . $estado['status_tracking'] . "</option>";
                        }
                      } else {
                        echo "<option value='" . $estado['status_tracking'] . "'>" . $estado['status_tracking'] . "</option>";
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 search-btn-content text-center">
                  <button type="buttom" class="btn btn-default buscar" pag="1" onclick="consulta_trazabilidad(1);"><i class="fa fa-search"></i>&nbsp;&nbsp;Buscar</button>
                </div>
              </div>
            </fieldset>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--end bridge-->
  <div class="mensaje"><?= $mensaje; ?></div>
  <div id="tabla_trazabilidad" class="table-responsive">
    <table class="table table-hover table-striped table-bordered table-condensed">
      <thead class="thead-dark">
        <th>ID</th>
        <th>Prefijo</th>
        <th>Folio</th>
        <th>Status</th>
        <th>Fecha</th>
        <th>Boton ver</th>
      </thead>
      <tbody style="text-align: center;">
      
<script type="text/javascript">
     function consulta_trazabilidad()   {

        alert('hola');
            //obtenemos el valor de los input
            var data_form = {
                prefijo: $(this).val('prefijo'),
                folio: $(this).val('folio'),
                status: $(this).val('status'),
                fecha: $(this).val('fecha'),
            }

           
            //se mando a llamar con javascript 
            $.ajax({
                url: 'php/consulta_trazabilidad.php',
                type: 'GET',
                dataType: 'json',
                data: {'prefijo':prefijo, 'folio':folio, 'status':status, 'fecha':fecha, },
              }).done(function(data) {

                if(status == '200'){
                    //insertar en tabla_trazabilidades
                  $('#traza').append(
                  '<tbody style="text-align: center;"><tr"><td class="traza">'
                  +data.prefijo+'</td><td class="traza">'+data.folio+'</td><td class="traza">'+data.status+'</td><td class="traza">'+data.fecha+'</td></tr></tbody>');

                }


                  
                })
                    
                
        
                
            };

</script>
        
          <tr>
            <td class="traza"><?= $traza['prefix'] ?></td>
            <td class="traza"><?= $traza['folio'] ?></td>
            <td class="traza"><?= $traza['status_tracking'] ?></td>
            <td class="traza"><?= $traza['created_at'] ?></td>
            <td>
              <a href="#modalDetalleTrazabilidad_<?= $traza['id_ws_trazabilidad'] ?>" class="btn btn-info" data-toggle="modal"><i class="fa fa-bars"></i>&nbsp;&nbsp;Ver Detalle</a>
            
              <?php if ($traza['status_tracking'] == "failed" and $total_detalle >= 2 and in_array($_SESSION['s_id_acceso'], $array_id_accesos) == true) : ?>
                <button class='btn btn-success desbloquear_folio' id='desbloquear_folio<?= $traza['id_ws_trazabilidad']; ?>' value='<?= $traza['id_ws_trazabilidad']; ?>'><i class='fa fa-check'></i>&nbsp;&nbsp;DESBLOQUEAR</button>
                <input class="unlock_varios" value='<?= $traza['id_ws_trazabilidad']; ?>' type="checkbox" id="chk_unlock<?= $traza['id_ws_trazabilidad']; ?>" name="chk_unlock<?= $traza['id_ws_trazabilidad']; ?>"> <label for="chk_unlock<?= $traza['id_ws_trazabilidad']; ?>">Desbloquear varios</label>
                <div id="alerta_trazabilidad"></div>
              <?php endif; ?>
            </td>
          </tr>
         
      </tbody>
    </table>
  </div>
  <div class="content-pagination"><?= $trazabilidad['paginacion_nav']; ?></div>

  <div id="div_Cliente" class="modal fade" tabindex="-1"></div>
</div>
<script type="text/javascript" src="<?php echo $siteURL . 'js/jquery331.min.js' ?>"></script>
<script type="text/javascript" language="javascript" src="<?php echo $siteURL . 'js/bootstrap.js' ?>"></script>
<script type="text/javascript" language="javascript" src="<?php echo $siteURL . 'js/bootstrap-confirmation.js' ?>"></script>
<!--   <script type="text/javascript" language="javascript" src="<?php echo $siteURL . 'js/sweetalert.js' ?>"></script> -->
<script type="text/javascript" language="javascript" src="<?php echo $siteURL . 'js/sweetalert.js' ?>"></script>
<!-- <script type="text/javascript" language="javascript" src="ajax.js"></script> -->
<!-- <script type="text/javascript" language="javascript" src="<?php echo $siteURL . 'trazabilidad/ajax.js' ?>"></script> -->
<script type="text/javascript">
  var rootURL = getRootUrl();

  function edit(id) {
    $('#modal_content').load(rootURL + 'trazabilidad/php/buscar_movimientos.php', {
      id: id
    });
  }

  function getRootUrl() {
    return window.location.origin ? window.location.origin + '/' : window.location.protocol + '/' + window.location.host + '/';
  }

  $(document).ready(function() {
    $('.desbloquear_folio').click(function() {
      //checar si hat varios seleccionados
      var array_folios = $(".unlock_varios:checked");
      if (array_folios.length > 0) {
        //varios
        var array_respuestas = [];
        $.when($.each(array_folios, function(index, element) {
          var id_ws_trazabilidad = $(element).val();
          $.ajax({
            url: 'php/actualiza_trazabilidad.php',
            async: false,
            data: {
              'id_ws_trazabilidad': id_ws_trazabilidad
            },
            type: 'POST',
            cache: false
          }).done(function(data) {
            array_respuestas.push(data);
          });
        })).then(function() {
          var errores = "";
          console.log(array_respuestas);
          $.when($.each(array_respuestas, function(index, element) {
            var confirmacion = element;
            if (confirmacion == false) {
              errores = "No se pudieron desbloquear todos los folios, la página se actualizará.";
            }
          })).then(function() {
            if (errores != "")
              $('#alerta_trazabilidad').html('<strong>ERROR!</strong> ' + errores).addClass('alert alert-danger');
            else
              $('#alerta_trazabilidad').html('Los folios han sido desbloqueados correctamente, la página se actualizará.').addClass('alert alert-success');
            window.setTimeout(function() {
              location.reload();
            }, 2000);
          });
        });
      } else {
        var id_ws_trazabilidad = $(this).val();
        $.ajax({
          url: 'php/actualiza_trazabilidad.php',
          data: {
            'id_ws_trazabilidad': id_ws_trazabilidad
          },
          type: 'POST',
          cache: false
        }).done(function(data) {
          var confirmacion = data;
          if (confirmacion == 'false') {
            $('#alerta_trazabilidad').html('<strong>ERROR!</strong> El folio no ha sido desbloqueado')
              .addClass('alert alert-danger');
            window.setTimeout(function() {
              $('#alerta_trazabilidad').fadeOut(500, 0).slideUp(500, function() {
                $(this).remove();
              });
            }, 2500);
            $('#tabla_trazabilidad').load(' #tabla_trazabilidad');
            location.reload();
          } else {
            $('#alerta_trazabilidad').html('El folio ha sido desbloqueado correctamente.').addClass('alert alert-success');
            window.setTimeout(function() {
              $('#alerta_trazabilidad').fadeOut(500, 0).slideUp(500, function() {
                $(this).remove();
              });
            }, 2500);
            $('#tabla_trazabilidad').load(' #tabla_trazabilidad');
            $('#desbloquear_folio').remove();
            location.reload();
          }
        });
      }
    });

  });
</script>
<?php
$page->endBody();
echo $page->render($_SERVER['DOCUMENT_ROOT'] . "/inc/template.php");
$mysqli->close();
?>