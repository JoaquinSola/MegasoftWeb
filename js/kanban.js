class KanbanBoard {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error('Container not found:', containerId);
            return;
        }
        this.cards = [];
        this.sections = [];
        this.draggingElement = null;
        this.init();
    }

    async init() {
        try {
            console.log('Initializing Kanban Board...');
            this.setupToolbar();
            await this.loadSections();
            await this.loadCards();
            this.setupEventListeners();
            console.log('Kanban Board initialized successfully');
        } catch (error) {
            console.error('Error initializing Kanban Board:', error);
        }
    }

    setupToolbar() {
        // Crear toolbar para gestión de secciones
        const existingToolbar = this.container.querySelector('.kanban-toolbar');
        if (existingToolbar) {
            existingToolbar.remove();
        }

        const toolbar = document.createElement('div');
        toolbar.className = 'kanban-toolbar';
        toolbar.innerHTML = `
            <button class="btn primary" onclick="window.kanban.addSection()">
                <i class="fas fa-plus"></i> Nueva Sección
            </button>
        `;
        this.container.prepend(toolbar);
    }

    async loadSections() {
        try {
            const response = await fetch('/WebMegasoft/MegasoftWeb/kanban/sections_api.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const sections = await response.json();
            console.log('Loaded sections:', sections); // Debug
            this.sections = sections;
            this.renderSections();
        } catch (error) {
            console.error('Error loading sections:', error);
            // Si hay un error, intentemos crear las secciones por defecto
            await this.createDefaultSections();
        }
    }

    async createDefaultSections() {
        const defaultSections = ['General', 'Desarrollo', 'Marketing'];
        for (const name of defaultSections) {
            await fetch('/WebMegasoft/MegasoftWeb/kanban/sections_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name,
                    position: defaultSections.indexOf(name)
                })
            });
        }
        // Recargar secciones después de crear las predeterminadas
        await this.loadSections();
    }

    async addSection() {
        const name = prompt('Nombre de la nueva sección:');
        if (!name) return;

        try {
            const position = this.sections.length;
            const response = await fetch('/WebMegasoft/MegasoftWeb/kanban/sections_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name, position })
            });
            
            const result = await response.json();
            if (result.success) {
                await this.loadSections();
            }
        } catch (error) {
            console.error('Error creating section:', error);
        }
    }

    async deleteSection(sectionId) {
        if (!confirm('¿Estás seguro de eliminar esta sección? Se eliminarán todas las tarjetas asociadas.')) {
            return;
        }

        try {
            const response = await fetch(`/WebMegasoft/MegasoftWeb/kanban/sections_api.php?id=${sectionId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                await this.loadSections();
                await this.loadCards();
            }
        } catch (error) {
            console.error('Error deleting section:', error);
        }
    }

    async loadCards() {
        try {
            const response = await fetch('/WebMegasoft/MegasoftWeb/kanban/api.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const cards = await response.json();
            console.log('Loaded cards:', cards); // Debug
            this.cards = cards;
            this.render();
        } catch (error) {
            console.error('Error loading cards:', error);
        }
    }

    async createCard(data) {
        try {
            const response = await fetch('/WebMegasoft/MegasoftWeb/kanban/api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                await this.loadCards();
            }
            return result;
        } catch (error) {
            console.error('Error creating card:', error);
            return { success: false, error };
        }
    }

    async addCard(sectionId, status) {
        try {
            console.log('Adding card to section:', sectionId, 'status:', status);
            const title = prompt('Título de la tarjeta:');
            if (!title) return;

            const description = prompt('Descripción (opcional):');
            const position = this.getMaxPosition(status, sectionId) + 1;

            console.log('Creating card with data:', {
                title,
                description,
                status,
                section_id: sectionId,
                position
            });

            const result = await this.createCard({
                title,
                description,
                status,
                section_id: sectionId,
                position
            });

            if (result.success) {
                console.log('Card created successfully');
                await this.loadCards(); // Recargar las tarjetas
            } else {
                console.error('Error creating card:', result.error);
                alert('Error al crear la tarjeta: ' + (result.error || 'Por favor, intente nuevamente.'));
            }
        } catch (error) {
            console.error('Error in addCard:', error);
            alert('Error al crear la tarjeta. Por favor, intente nuevamente.');
        }
    }

    async updateCard(id, data) {
        try {
            const response = await fetch(`/WebMegasoft/MegasoftWeb/kanban/api.php?id=${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                await this.loadCards();
            }
            return result;
        } catch (error) {
            console.error('Error updating card:', error);
            return { success: false, error };
        }
    }

    async deleteCard(id) {
        try {
            const response = await fetch(`/WebMegasoft/MegasoftWeb/kanban/api.php?id=${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();
            if (result.success) {
                await this.loadCards();
            }
            return result;
        } catch (error) {
            console.error('Error deleting card:', error);
            return { success: false, error };
        }
    }

    setupEventListeners() {
        // Delegación de eventos para el drag & drop
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('kanban-card')) {
                this.draggingElement = e.target;
                e.target.classList.add('dragging');
            }
        });

        this.container.addEventListener('dragend', (e) => {
            if (e.target.classList.contains('kanban-card')) {
                e.target.classList.remove('dragging');
                this.draggingElement = null;
            }
        });

        this.container.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!this.draggingElement) return;
            
            const column = e.target.closest('.kanban-column');
            if (column) {
                const status = column.dataset.status;
                const cards = column.querySelectorAll('.kanban-card:not(.dragging)');
                let newPosition = this.getNewPosition(cards, e.clientY);
                
                this.updateCard(this.draggingElement.dataset.id, {
                    status,
                    position: newPosition
                });
            }
        });
        // No listeners para .add-card-btn aquí, se usan los onclick del HTML generado
    }

    getNewPosition(cards, mouseY) {
        const card = [...cards].reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = mouseY - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;

        if (card) {
            return parseInt(card.dataset.position) + 1;
        } else {
            return 0;
        }
    }

    getMaxPosition(status, sectionId) {
        const columnCards = this.cards.filter(
            card => card.status === status && card.section_id === sectionId
        );
        if (columnCards.length === 0) return 0;
        return Math.max(...columnCards.map(card => card.position));
    }

    renderSections() {
        const sectionsContainer = document.createElement('div');
        sectionsContainer.className = 'kanban-sections';
        
        this.sections.forEach(section => {
            const sectionHTML = `
                <div class="kanban-section" data-section-id="${section.id}">
                    <div class="section-header">
                        <h2>${section.name}</h2>
                        <button onclick="kanban.deleteSection(${section.id})" class="delete-btn">×</button>
                    </div>
                    <div class="kanban-columns">
                        <div class="kanban-column" data-status="todo" data-section-id="${section.id}">
                            <h3>Por Hacer</h3>
                            <div class="kanban-cards"></div>
                            <button class="add-card-btn" onclick="window.kanban.addCard(${section.id}, 'todo')">
                                + Agregar Tarjeta
                            </button>
                        </div>
                        
                        <div class="kanban-column" data-status="doing" data-section-id="${section.id}">
                            <h3>En Progreso</h3>
                            <div class="kanban-cards"></div>
                            <button class="add-card-btn" onclick="window.kanban.addCard(${section.id}, 'doing')">
                                + Agregar Tarjeta
                            </button>
                        </div>
                        
                        <div class="kanban-column" data-status="done" data-section-id="${section.id}">
                            <h3>Completado</h3>
                            <div class="kanban-cards"></div>
                            <button class="add-card-btn" onclick="window.kanban.addCard(${section.id}, 'done')">
                                + Agregar Tarjeta
                            </button>
                        </div>
                    </div>
                </div>
            `;
            sectionsContainer.insertAdjacentHTML('beforeend', sectionHTML);
        });

        // Limpiar y actualizar el contenedor
        const existingSections = this.container.querySelector('.kanban-sections');
        if (existingSections) {
            existingSections.remove();
        }
        this.container.appendChild(sectionsContainer);
    }

    render() {
        this.sections.forEach(section => {
            const sectionId = parseInt(section.id);
            const sectionCards = this.cards.filter(card => parseInt(card.section_id) === sectionId);
            const columns = {
                todo: sectionCards.filter(card => card.status === 'todo'),
                doing: sectionCards.filter(card => card.status === 'doing'),
                done: sectionCards.filter(card => card.status === 'done')
            };

            Object.entries(columns).forEach(([status, cards]) => {
                const column = this.container.querySelector(
                    `.kanban-column[data-status="${status}"][data-section-id="${section.id}"]`
                );
                if (column) {
                    const cardsContainer = column.querySelector('.kanban-cards');
                    cardsContainer.innerHTML = cards
                        .sort((a, b) => a.position - b.position)
                        .map(card => this.createCardHTML(card))
                        .join('');
                }
            });
        });
    }

    createCardHTML(card) {
        return `
            <div class="kanban-card" 
                 draggable="true" 
                 data-id="${card.id}"
                 data-position="${card.position}">
                <div class="card-header">
                    <h3>${card.title}</h3>
                    <button onclick="kanban.deleteCard(${card.id})" class="delete-btn">×</button>
                </div>
                <p>${card.description || ''}</p>
                <div class="card-footer">
                    <span class="timestamp">
                        ${new Date(card.created_at).toLocaleDateString()}
                    </span>
                </div>
            </div>
        `;
    }
}

// Inicializar el tablero cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.kanban = new KanbanBoard('kanban-board');
});