/**
 * Función universal para activar DataTables y exportación en cualquier tabla.
 * 
 * @param {string} idTabla - El ID de la tabla HTML (ej: '#tablaOrdenes')
 * @param {string} tituloReporte - El título que irá dentro del Excel y PDF
 * @param {Array} excluirColumnas - Arreglo con índices de columnas a ignorar (ej: [7, 8])
 * @param {boolean} mostrarBuscador - true para Proveedor (con filtros), false para Cliente (solo ticket)
 */
function activarExportacion(idTabla, tituloReporte, excluirColumnas = [], mostrarBuscador = true) {
    
    // Verificamos si la tabla existe en la pantalla actual
    if ($(idTabla).length) {
        
        // 1. Configuramos qué columnas ignorar (como la columna de botones "Acciones")
        let opcionesExportacion = {};
        if (excluirColumnas.length > 0) {
            // Mágia de jQuery para decirle: "Exporta todo MENOS las columnas en esta lista"
            opcionesExportacion = { columns: ':not(' + excluirColumnas.map(c => ':eq(' + c + ')').join(',') + ')' };
        } else {
            opcionesExportacion = { columns: ':visible' }; // Por defecto exporta todo lo visible
        }

        // 2. Configuramos la vista de la tabla
        // 'Bfrtip' = Muestra Botones, Filtro(Buscador), Procesando, Tabla, Info, Paginación
        // 'B' = Muestra SOLAMENTE los Botones (Ideal para el ticket del cliente)
        let configuracionDom = mostrarBuscador ? 'Bfrtip' : 'B';

        // 3. Inicializamos DataTables
        $(idTabla).DataTable({
            dom: configuracionDom,
            paging: mostrarBuscador,      // Paginación activada/desactivada
            searching: mostrarBuscador,   // Buscador (Filtro) activado/desactivado
            info: mostrarBuscador,        // Texto "Mostrando 1 a 10 de X"
            ordering: mostrarBuscador,    // Flechas para ordenar por orden alfabético
            
            // Configuración de los botones de exportación
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '📊 Exportar a Excel',
                    className: 'btn-exportar', // Clase para darle estilo CSS
                    title: tituloReporte,
                    exportOptions: opcionesExportacion
                },
                {
                    extend: 'pdfHtml5',
                    text: '📄 Exportar a PDF',
                    className: 'btn-exportar',
                    title: tituloReporte,
                    orientation: 'landscape', // Hoja en horizontal
                    pageSize: 'A4',
                    exportOptions: opcionesExportacion
                }
            ],
            // Traducir todos los textos de la tabla al español
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });
    }
}