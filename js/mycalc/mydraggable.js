
    // Get the draggable div element
    const draggableDiv = document.getElementById('draggableDiv');
    const minimizeButton = document.querySelector('.minimize-button');
    const closeButton = document.getElementById('closeButton');
    const calculatorInput = document.getElementById('screen');
    let timeoutId;


    // Function to handle the button click event
    function openDiv() {
      draggableDiv.classList.remove('minimized');
      draggableDiv.style.display = 'block';
      // $('#screen').focus();
      timeoutId = setTimeout(() => {
        calculatorInput.focus();
      }, 10);
    }

    // Function to minimize the draggable div
    function minimizeDiv() {
      draggableDiv.classList.toggle('minimized');
      calculatorInput.focus();
    }

    // Function to close the draggable div
    function closeDiv() {
      draggableDiv.style.display = 'none';
      clearTimeout(timeoutId);
    }

    // Function to make the div draggable and restrict movement within the window
    function makeDivDraggable() {
      let pos1 = 0,
          pos2 = 0,
          pos3 = 0,
          pos4 = 0;

      draggableDiv.onmousedown = dragMouseDown;

      function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
      }

      function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;

        // Restrict movement within the window boundaries
        const windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        const windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

        const maxX = windowWidth - draggableDiv.offsetWidth;
        const maxY = windowHeight - draggableDiv.offsetHeight;

        let newPosX = draggableDiv.offsetLeft - pos1;
        let newPosY = draggableDiv.offsetTop - pos2;

        newPosX = Math.max(0, Math.min(newPosX, maxX));
        newPosY = Math.max(0, Math.min(newPosY, maxY));

        draggableDiv.style.top = newPosY + "px";
        draggableDiv.style.left = newPosX + "px";
      }

      function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
      }
    }

    // Attach event listeners
    const openButton = document.getElementById('openButton');
    openButton.addEventListener('click', openDiv);

    minimizeButton.addEventListener('click', minimizeDiv);
    closeButton.addEventListener('click', closeDiv);
    makeDivDraggable();