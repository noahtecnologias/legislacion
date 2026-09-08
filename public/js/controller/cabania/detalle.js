$(document).ready(function() {
    init();

    // Función que agrega un 0 al día o al mes
    function addZero(n)
    {
        if (parseInt(n)< 10) {
            return '0'+n;
        }
        return n;
    }

    // Función que formatea la fecha para que FullCalendar lo pueda interpretar
    function formatDate(fecha, band)
    {
        let fechaAux = new Date(fecha);
        // Si es fecha hasta se suma un día más para que lo marque el ultimo día.
        if (band) {
            fechaAux.setDate(fechaAux.getDate() + 1);
        }
        fechaAux.setMinutes(fechaAux.getMinutes() + fechaAux.getTimezoneOffset())
        var d = addZero(fechaAux.getDate());
        var m = addZero(fechaAux.getMonth());
        if (m == 0) {
            m = '0'+1;
        } else {
            m = m+1;
        }
        var y = fechaAux.getFullYear();
        return y+'-'+m+'-'+d;
    }

    //Funcion que arma el calendario con los datos
    function buildCalendar(id, datos)
    {
        let event = [];
        let cabania = document.getElementById('cabania');
        if (datos.length > 0) {
            datos.forEach(dato => {
                let colorAux = '#D11D1D'; // red
                if (dato.origen == 'Booking') { // yelow
                    colorAux = '#E4EB10'
                }
                event.push(
                    {
                        title: '',
                        start: formatDate(dato.fecha_desde, false),
                        end: formatDate(dato.fecha_hasta, true),
                        color: colorAux,
                        display: 'background',
                        url: baseUrlByRol + 'cabania/'+id+'/reserva/'+dato.id
                    }
                )
            });
        }
        
        let cabaniaCalendar = new FullCalendar.Calendar(cabania, {
            locale: 'es',
            timeZone: 'local',
            headerToolbar: {
                right: 'prev,next'
            },
            initialView: 'dayGridMonth',
            events: event,
            eventClick: function (info) {
                // Evitar la acción predeterminada (si existe un enlace)
                info.jsEvent.preventDefault();
                // Acceso a los datos del evento
                var eventObj = info.event;
                // Ejemplo: Redirigir a una URL definida en el evento (opcional)
                if (eventObj.url) {
                    window.location = eventObj.url;
                }
            },
        });
        cabaniaCalendar.render();

    }

    // Funcion que carga las reservas en el calendario
    function loadCalendario(cabaniaId)
    {
        // Realizar una solicitud AJAX cuando la página se carga
        $(".loadFones").addClass("working");
        $.ajax({
            url: baseUrlByRol + 'ajax/cabania/'+cabaniaId+'/reservas',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $(".loadFones").removeClass("working");
                let reservas = JSON.parse(data.data);
                buildCalendar(cabaniaId, reservas);
            },
            error: function(xhr, status, error) {
                $(".loadFones").removeClass("working");
                swal("Ocurrio un error", "Hubo un error al obtener los datos de los usuarios.", "error");
            }
        });
    }

    function init()
    {
        let cabaniaId = $("#cabania_id").val(); // Obtengo el Id de la cabania.
        loadCalendario(cabaniaId);
    }
});