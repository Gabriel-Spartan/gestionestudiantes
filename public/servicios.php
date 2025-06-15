<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/auth_check.php';
?>

<div class="container mt-4">
    <h1>Servicios</h1>
    <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTA</title>
</head>

<body>
    <section>
        <h2>Aplicación Cuarto Software</h2>

        <table id="dg" title="Mis Estudiantes" class="easyui-datagrid" style="width:700px;height:250px"
            url="Models/select.php" toolbar="#toolbar" pagination="true" rownumbers="true" fitColumns="true"
            singleSelect="true">
            <thead>
                <tr>
                    <th field="cedula" width="50">Cedula</th>
                    <th field="nombre" width="50">Nombre</th>
                    <th field="apellido" width="50">Apellido</th>
                    <th field="direccion" width="50">Direccion</th>
                    <th field="telefono" width="50">Telefono</th>
                </tr>
            </thead>
        </table>
        <div id="toolbar">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                onclick="newUser()">Nuevo Estudiante</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
                onclick="editUser()">Editar Estudiante</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
                onclick="destroyUser()">Eliminar Estudiante</a>
        </div>

        <div id="dlg" class="easyui-dialog" style="width:400px"
            data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
            <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                <h3>Información de Estudiante:</h3>
                <div style="margin-bottom:10px">
                    <input name="cedula" class="easyui-textbox" required="true" label="Cédula:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="nombre" class="easyui-textbox" required="true" label="Nombre:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="apellido" class="easyui-textbox" required="true" label="Apellido:" style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="direccion" class="easyui-textbox" required="true" label="Dirección:"
                        style="width:100%">
                </div>
                <div style="margin-bottom:10px">
                    <input name="telefono" class="easyui-textbox" required="true" label="Teléfono:" style="width:100%">
                </div>
            </form>
        </div>
        <div id="dlg-buttons">
            <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()"
                style="width:90px">Guardar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
                onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancelar</a>
        </div>
        <script type="text/javascript">
           /* var url;
            function newUser() {
                $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Nuevo Estudiante');
                $('#fm').form('clear');
                url = 'Models/guardar.php';
            }
            function editUser() {
                var row = $('#dg').datagrid('getSelected');
                if (row) {
                    $('#dlg').dialog('open').dialog('center').dialog('setTitle', 'Editar Estudiante');
                    $('#fm').form('load', {
                        cedula: row.cedula,
                        nombre: row.nombre,
                        apellido: row.apellido,
                        telefono: row.telefono,
                        direccion: row.direccion
                    });
                    url = 'Models/editar.php?cedula=' + row.cedula;
                }
            }
            function saveUser() {
                $('#fm').form('submit', {
                    url: url,
                    iframe: false,
                    onSubmit: function () {
                        return $(this).form('validate');
                    },
                    success: function (result) {
                        var result = eval('(' + result + ')');
                        if (result.errorMsg) {
                            $.messager.show({
                                title: 'Error',
                                msg: result.errorMsg
                            });
                        } else {
                            $.messager.show({
                                title: 'Se guardo correctamente',
                                msg: result.errorMsg
                            });
                            $('#dlg').dialog('close');        // close the dialog
                            $('#dg').datagrid('reload');    // reload the user data
                        }
                    }
                });
            }
            function destroyUser() {
                var row = $('#dg').datagrid('getSelected');
                if (row) {
                    $.messager.confirm('Confirm', 'Estás seguro de eliminar el estudiante?', function (r) {
                        if (r) {
                            $.post('Models/eliminar.php', { cedula: row.cedula }, function (result) {
                                if (result.success) {
                                    $.messager.show({    // show error message
                                        title: 'Error',
                                        msg: result.errorMsg
                                    });
                                } else {
                                    $.messager.show({    // show error message
                                        title: 'Usuario eliminado correctamente',
                                        msg: result.errorMsg
                                    });
                                    $('#dg').datagrid('reload');    // reload the user data
                                }
                            }, 'json');
                        }
                    });
                }
            }*/
        </script>
    </section>

</body>

</html>
</div>

<?php require_once 'includes/footer.php'; ?>