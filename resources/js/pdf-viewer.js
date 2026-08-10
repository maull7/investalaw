import * as pdfjsLib from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

export default function pdfViewer(pdfUrl, fitWidth = false) {
    // ponytail: objek pdf.js (PDFDocumentProxy) TIDAK boleh masuk properti reaktif Alpine —
    // proxy merusak private field js (#pagePromises). Simpan di closure, non-reaktif.
    let pdfDoc = null;

    return {
        loaded: false,
        currentPage: 1,
        totalPages: 0,
        scale: 1.2,
        showingScroll: false,
        rendering: false,
        dragStartX: null,
        dragActive: false,
        error: null,
        isFullscreen: false,

        async init() {
            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
            });
            try {
                pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
                this.totalPages = pdfDoc.numPages;
                this.loaded = true;
                await this.renderPage(this.currentPage);
                if (fitWidth) {
                    this.$nextTick(() => this.fitToWidth());
                }
            } catch (e) {
                console.error('PDF load error:', e);
                this.error = 'Gagal memuat dokumen PDF. File mungkin tidak tersedia atau rusak.';
            }
        },

        fitToWidth() {
            const wrapper = this.$refs.slideWrapper?.clientWidth;
            const canvas = this.$refs.pdfCanvas?.clientWidth;
            if (wrapper && canvas && !this.rendering) {
                this.scale = Math.min(this.scale * (wrapper / canvas), 3);
                this.renderPage(this.currentPage);
            }
        },

        async renderPage(pageNum) {
            if (this.rendering || !pdfDoc) return;
            this.rendering = true;
            this.currentPage = pageNum;

            try {
                const page = await pdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: this.scale });
                const canvas = this.$refs.pdfCanvas;
                const context = canvas.getContext('2d');

                canvas.height = viewport.height;
                canvas.width = viewport.width;

                await page.render({
                    canvasContext: context,
                    viewport: viewport,
                }).promise;
            } catch (e) {
                console.error('Render error:', e);
            } finally {
                this.rendering = false;
            }
        },

        goToPage(page) {
            const p = Math.max(1, Math.min(page, this.totalPages));
            this.renderPage(p);
        },

        prevPage() {
            if (this.currentPage > 1) this.renderPage(this.currentPage - 1);
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.renderPage(this.currentPage + 1);
        },

        zoomIn() {
            this.scale = Math.min(this.scale + 0.2, 3);
            this.applyZoom();
        },

        zoomOut() {
            this.scale = Math.max(this.scale - 0.2, 0.5);
            this.applyZoom();
        },

        applyZoom() {
            if (this.showingScroll) {
                this.renderScrollPages();
            } else {
                this.renderPage(this.currentPage);
            }
        },

        toggleScroll() {
            this.showingScroll = !this.showingScroll;
            if (this.showingScroll && this.$refs.scrollContainer.childElementCount === 0) {
                this.renderScrollPages();
            }
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                this.$root.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        },

        async renderScrollPages() {
            if (!pdfDoc) return;
            this.rendering = true;
            const container = this.$refs.scrollContainer;
            container.innerHTML = '';

            try {
                for (let p = 1; p <= this.totalPages; p++) {
                    const page = await pdfDoc.getPage(p);
                    const viewport = page.getViewport({ scale: this.scale });
                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    canvas.className = 'mx-auto shadow-lg bg-white';
                    container.appendChild(canvas);
                    await page.render({
                        canvasContext: canvas.getContext('2d'),
                        viewport: viewport,
                    }).promise;
                }
            } catch (e) {
                console.error('Scroll render error:', e);
            } finally {
                this.rendering = false;
            }
        },

        dragStart(e) {
            if (this.showingScroll) return;
            this.dragStartX = e.clientX ?? e.touches?.[0]?.clientX;
            this.dragActive = true;
        },

        dragEnd(e) {
            if (!this.dragActive || this.showingScroll) return;
            this.dragActive = false;
            const endX = e.clientX ?? e.changedTouches?.[0]?.clientX;
            const delta = endX - this.dragStartX;
            if (delta < -40) {
                this.nextPage();
            } else if (delta > 40) {
                this.prevPage();
            }
        },
    };
}