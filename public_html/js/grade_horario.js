/**
 * Para funcionar é necessario chamar a funcao criagrade
 *
 */

function criaGrade(resposta, idTabela) {
    let obj = JSON.parse(resposta);

    var timetable = new TimetableMin();
    timetable.setScope(8, 22); // optional, only whole hours between 0 and 23
    timetable.addLocations(['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta']);

    if (obj.length < 1) {
        /*TODO: Colocar uma mensagem ou não?*/
    } else {
        //console.log(obj);
        for (i in obj) {
            let hini = obj[i].inicio.split(':');
            let hfim = obj[i].fim.split(':');
            //console.log( obj[i] );
            aux=obj[i].oficina + " " + obj[i].turma;
            let dia = qualDia(obj[i]);
            for (j in dia) {
                timetable.addEvent(
                    aux,
                    dia[j],
                    new Date(
                        1970,
                        1,
                        1,
                        parseInt(hini[0]),
                        parseInt(hini[1])

                        //parseInt( obj[0].inicio.split(':')[1] )
                    ),
                    new Date(
                        1970,
                        1,
                        1,
                        parseInt(hfim[0]),
                        parseInt(hfim[1])
                    )
                );
            }
        }
    }

    /*Rendereer*/
    var renderer = new TimetableMin.Renderer(timetable);
    renderer.draw('.timetable'); // any css selector

    var beauty = $('span.time-entry').map(function(){
        if( $(this).width() < 27 ){
            $(this).children("small").empty();
            $(this).children("small").append("...");
        }
    });

    console.log(beauty);
}

//const aproxima = (horario, valor) => horario.split(':')[0] + ":" + (parseInt(horario.split('h')[0].split(':')[1]) + valor) + "h";

/*todo: no futuro, mudar a implementação do json para dia:segunda ou dia:nº*/
function qualDia(obj) {
    let days = [];
    if (obj.segunda == 1) {
        days.push("Segunda");
    }
    if (obj.terca == 1) {
        days.push("Terça");
    }
    if (obj.quarta == 1) {
        days.push("Quarta");
    }
    if (obj.quinta == 1) {
        days.push("Quinta");
    }
    if (obj.sexta == 1) {
        days.push("Sexta");
    }
    /*else {
        console.log("grade_horário: erro nos dias da semana");
        return null;
    }*/

    return days;
}
