/**
 * Función universal para activar DataTables y exportación en cualquier tabla.
 */
function activarExportacion(idTabla, tituloReporte, excluirColumnas = [], mostrarBuscador = true) {
    
    if ($(idTabla).length) {
        
        // 1. Configuramos qué columnas ignorar
        let opcionesExportacion = {};
        if (excluirColumnas.length > 0) {
            opcionesExportacion = { columns: ':not(' + excluirColumnas.map(c => ':eq(' + c + ')').join(',') + ')' };
        } else {
            opcionesExportacion = { columns: ':visible' }; 
        }

        // 2. Configuramos la vista (sin buscador feo)
        let configuracionDom = mostrarBuscador ? 'Brtip' : 'B';

        // 3. Inicializamos DataTables
        let tabla = $(idTabla).DataTable({
            dom: configuracionDom,
            paging: mostrarBuscador,      
            searching: true, 
            info: mostrarBuscador,        
            ordering: mostrarBuscador,    
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '📊 Exportar a Excel',
                    className: 'btn-exportar',
                    title: tituloReporte,
                    exportOptions: opcionesExportacion
                },
                {
                    extend: 'pdfHtml5',
                    text: '📄 Exportar a PDF',
                    className: 'btn-exportar',
                    title: tituloReporte,
                    orientation: 'landscape', 
                    pageSize: 'A4',
                    exportOptions: opcionesExportacion,
                    
                    // ============================================================
                    // NUEVO: MAGIA PARA CENTRAR Y EXPANDIR EL PDF AL 100%
                    // ============================================================
                    customize: function(doc) {
                        // doc.content[1] es la tabla en la estructura del PDF generado
                        let tablaPdf = doc.content[1].table;
                        
                        // 1. Forzar que la tabla ocupe el 100% del ancho
                        // Contamos cuántas columnas se están exportando
                        let numColumnas = tablaPdf.body[0].length;
                        let anchos = [];
                        for (let i = 0; i < numColumnas; i++) {
                            anchos.push('*'); // El '*' significa que se distribuya equitativamente
                        }
                        tablaPdf.widths = anchos;

                        // 2. Centrar los encabezados (Títulos verdes)
                        doc.styles.tableHeader.alignment = 'center';
                        
                        // 3. Centrar el texto de todas las celdas (filas)
                        doc.content[1].table.body.forEach(function(row) {
                            row.forEach(function(cell) {
                                cell.alignment = 'center'; // Alinea el texto al medio
                            });
                        });
                    }
                    // ============================================================
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });

        return tabla;
    }
    return null;
}