const draggables = document.querySelectorAll('.item')
const containers = document.querySelectorAll('.list')

draggables.forEach(draggable => {
    draggable.addEventListener('dragstart', () => {
        draggable.classList.add('dragging');
        draggable.classList.add('active');
        draggable.style.opacity = '0.5'
    })

    draggable.addEventListener('dragend', () => {
        draggable.classList.remove('dragging')
        draggable.classList.remove('active');
        draggable.style.opacity = null
        getListItemPosition()
    })
})

containers.forEach(container => {
    container.addEventListener('dragover', e => {
        e.preventDefault();
        const afterElement = getDragAfterElement(container, e.clientY)
        const draggable = document.querySelector('.dragging')
        if (afterElement == null) {
            container.appendChild(draggable)

        } else {
            container.insertBefore(draggable, afterElement)
        }
    })
})

function getDragAfterElement(container, y) {
    const draggableElements  = [...container.querySelectorAll('.item:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect()
        const offset = y - box.top - box.height / 2
        if (offset < 0 && offset > closest.offset){
            return {offset : offset, element: child}
        } else {
            return closest
        }
    }, {offset: Number.NEGATIVE_INFINITY}).element;
}

function getListItemPosition() {
    const listitem = document.querySelectorAll('.item')
    console.log(Object.keys(listitem).length)
}