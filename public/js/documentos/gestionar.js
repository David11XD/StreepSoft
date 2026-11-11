// Gestion persistente de documentos de jugadores (API PHP + archivos privados).
const datos = DATOS_TARJETA;
const estado = {
    documentos: datos.documentos.map(d => ({...d})),
    seleccionado: datos.documentos[0]?.clave || 'cedula',
    confirmacion: null,
    motivo: 'Ilegible'
};
const $ = id => document.getElementById(id);
const filas = $('tdocFilas');
const detalle = $('tdocDetalle');
const aviso = $('tdocAviso');
let timerAviso;

const ETIQUETA = { pendiente:'Pendiente', verificar:'Por verificar', verificado:'Verificado', rechazado:'Rechazado' };
const docActual = () => estado.documentos.find(d => d.clave === estado.seleccionado);
const tamano = bytes => bytes < 1048576 ? Math.max(1,Math.round(bytes/1024))+' KB' : (bytes/1048576).toFixed(1)+' MB';

function mostrarAviso(msg, tipo='ok') {
    aviso.textContent=msg; aviso.className='tdoc-aviso aviso-'+tipo; aviso.hidden=false;
    clearTimeout(timerAviso); timerAviso=setTimeout(()=>aviso.hidden=true,4000);
}
async function enviar(datos, archivo=null) {
    const fd = new FormData();
    Object.entries(datos).forEach(([k,v])=>fd.append(k,v ?? ''));
    fd.append('_token', datos.csrfToken || datos._token || '');
    if (archivo) fd.append('archivo',archivo);
    const r=await fetch('/streepsoft/documentos/accion',{method:'POST',body:fd,credentials:'same-origin'});
    const json=await r.json().catch(()=>({ok:false,mensaje:'El servidor no devolvió una respuesta válida.'}));
    if (!r.ok || !json.ok) throw new Error(json.mensaje || 'No se pudo completar la operación.');
    return json;
}
function pintarChip(el, status) {
    el.className='tdoc-chip chip-'+status;
    el.textContent=ETIQUETA[status] || status;
}
function dibujar() {
    estado.documentos.forEach(doc=>{
        const fila=filas.querySelector(`[data-clave="${doc.clave}"]`);
        const meta=fila.querySelector('.tdoc-fila-meta');
        fila.classList.toggle('seleccionada',doc.clave===estado.seleccionado);
        fila.setAttribute('aria-current',doc.clave===estado.seleccionado?'true':'false');
        fila.querySelector('.tdoc-circulo').className='tdoc-circulo circulo-'+doc.estado;
        pintarChip(fila.querySelector('.tdoc-chip'),doc.estado);
        meta.textContent=doc.estado==='pendiente'?'Sin archivo':`${doc.archivo} · ${doc.tamano}`;
    });
    const doc=docActual(); if(!doc)return;
    detalle.querySelectorAll('[data-estados]').forEach(el=>{
        el.hidden=!el.dataset.estados.split(' ').includes(doc.estado);
    });
    $('tdocNombre').textContent=doc.nombreCompleto;
    $('tdocDescripcion').textContent=doc.descripcion;
    pintarChip($('tdocChip'),doc.estado);
    $('tdocAlerta').hidden=doc.estado!=='rechazado';
    $('tdocAlertaTexto').textContent=doc.estado==='rechazado'
        ? `Documento rechazado. Motivo: ${doc.motivoRechazo || 'No especificado'}. Puedes reemplazarlo por uno nuevo.`:'';
    $('tdocImagen').hidden=true; $('tdocImagen').src='';
    $('tdocHoja').hidden=doc.estado==='pendiente';
    $('tdocNotaVista').hidden=doc.estado==='pendiente';
    $('tdocPdfPreview').hidden=doc.estado==='pendiente';
    if(doc.estado!=='pendiente' && doc.url){
        $('tdocHoja').hidden=true;
        $('tdocPdfPreview').src=doc.url+'#toolbar=0&navpanes=0&view=FitH';
        $('tdocNotaVista').textContent='Vista previa del PDF. Utiliza Abrir para consultar el documento completo.';
    } else {
        $('tdocExtension').textContent='PDF';
        $('tdocNotaVista').textContent='Todavía no se ha cargado un archivo.';
    }
    if(doc.estado!=='pendiente'){
        $('tdocArchivo').textContent=doc.archivo;
        $('tdocTamano').textContent=doc.tamano;
        $('tdocSubido').textContent=`${doc.fechaSubida} · ${doc.subidoPor}`;
        $('tdocVerificacion').textContent=doc.estado==='verificado'
            ? `${doc.fechaVerificacion} · ${doc.verificadoPor}`
            : doc.estado==='rechazado' ? `Rechazado · ${doc.motivoRechazo || ''}` : 'Pendiente de verificar';
        $('tdocRuta').textContent='Archivo privado del sistema';
    }
    $('tdocConfirmarRechazo').hidden=estado.confirmacion!=='rechazo';
    $('tdocConfirmarQuitar').hidden=estado.confirmacion!=='quitar';
    $('tdocMotivo').value=estado.motivo;
    actualizarResumen();
}
function actualizarResumen(){
    const docs=estado.documentos;
    const verificados=docs.filter(d=>d.estado==='verificado').length;
    const faltan=docs.filter(d=>d.estado==='pendiente').map(d=>d.nombre);
    const revisar=docs.filter(d=>d.estado==='verificar').map(d=>d.nombre);
    const rechazados=docs.filter(d=>d.estado==='rechazado').map(d=>d.nombre);
    let clase='completo', texto='Completo', mensaje='Todos los documentos están verificados';
    if(faltan.length){clase='incompleto';texto='Incompleto';mensaje='Falta subir: '+faltan.join(', ');}
    else if(rechazados.length){clase='incompleto';texto='Rechazado';mensaje='Documentos rechazados: '+rechazados.join(', ');}
    else if(revisar.length){clase='verificar';texto='Por verificar';mensaje='Falta verificar: '+revisar.join(', ');}
    document.querySelector('.ges-progreso-conteo').textContent=`${verificados} de 4 verificados · ${4-faltan.length} entregados`;
    const chip=document.querySelector('.ges-etiqueta-progreso');
    chip.className='ges-etiqueta-progreso progreso-'+clase;chip.textContent=texto;
    document.querySelector('.ges-progreso-falta').textContent=mensaje;
    document.querySelectorAll('.ges-segmento').forEach((el,i)=>el.className='ges-segmento segmento-'+docs[i].estado);
}
async function subir(file){
    if(!file)return;
    if(file.type!=='application/pdf' || !/\.pdf$/i.test(file.name)){mostrarAviso('Solo se permiten archivos PDF.','error');return;}
    if(file.size>10*1024*1024){mostrarAviso('El archivo supera el máximo de 10 MB.','error');return;}
    const doc=docActual();
    try{
        const result=await enviar({accion:'subir',jugador_id:datos.jugadorId,clave:doc.clave,csrfToken:datos.csrfToken},file);
        mostrarAviso(result.mensaje);
        await recargar();
    }catch(e){mostrarAviso(e.message,'error');}
}
async function recargar(){
    const r=await fetch(`/streepsoft/documentos/datos/${datos.jugadorId}`,{credentials:'same-origin'});
    const json=await r.json();
    if(!r.ok || !json.ok) throw new Error(json.mensaje || 'No se pudo actualizar la información.');
    estado.documentos=json.documentos;
    datos.documentos=json.documentos;
    dibujar();
}

async function accion(accion,extra={}){
    const doc=docActual();
    try{
        const result=await enviar({accion,jugador_id:datos.jugadorId,clave:doc.clave,documento_id:doc.id,csrfToken:datos.csrfToken,...extra});
        estado.confirmacion=null;mostrarAviso(result.mensaje);await recargar();
    }catch(e){mostrarAviso(e.message,'error');}
}
function abrir(descargar=false){
    const doc=docActual();if(!doc.id)return;
    const url=`/streepsoft/documentos/archivo/${doc.id}?modo=${descargar?'descargar':'preview'}`;
    if(descargar){const a=document.createElement('a');a.href=url;a.download=doc.archivo;a.click();}
    else window.open(url,'_blank','noopener');
}

filas.addEventListener('click',e=>{
    const fila=e.target.closest('.tdoc-fila');if(fila){estado.seleccionado=fila.dataset.clave;estado.confirmacion=null;dibujar();}
});
detalle.addEventListener('change',e=>{
    if(e.target.matches('.tdoc-input-archivo')){subir(e.target.files[0]);e.target.value='';}
    if(e.target.id==='tdocMotivo')estado.motivo=e.target.value;
});
detalle.addEventListener('dragover',e=>e.preventDefault());
detalle.addEventListener('drop',e=>{e.preventDefault();subir(e.dataTransfer.files[0]);});
filas.addEventListener('dragover',e=>e.preventDefault());
filas.addEventListener('drop',e=>{e.preventDefault();const f=e.target.closest('.tdoc-fila');if(f){estado.seleccionado=f.dataset.clave;subir(e.dataTransfer.files[0]);}});
detalle.addEventListener('click',e=>{
    const b=e.target.closest('[data-accion]');if(!b)return;
    const a=b.dataset.accion;
    if(a==='verificar')accion('verificar');
    else if(a==='revertir')accion('revertir');
    else if(a==='abrir-rechazo'){estado.confirmacion='rechazo';dibujar();}
    else if(a==='confirmar-rechazo')accion('rechazar',{motivo:estado.motivo});
    else if(a==='preguntar-quitar'){estado.confirmacion='quitar';dibujar();}
    else if(a==='confirmar-quitar')accion('eliminar');
    else if(a==='cancelar'){estado.confirmacion=null;dibujar();}
    else if(a==='descargar')abrir(true);
    else if(a==='abrir')abrir(false);
});
$('tdocSiguiente').addEventListener('click',()=>{
    const docs=estado.documentos;const pos=docs.findIndex(d=>d.clave===estado.seleccionado);
    const next=docs.slice(pos+1).concat(docs.slice(0,pos+1)).find(d=>d.estado!=='verificado');
    if(next){estado.seleccionado=next.clave;dibujar();}else mostrarAviso('Todos los documentos están verificados.');
});
dibujar();
