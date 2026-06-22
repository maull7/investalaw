import * as pdfjsLib from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

export default function pdfViewer(pdfUrl) {
    return {
        pdfDoc: null,
        currentPage: 1,
        totalPages: 0,
        scale: 1.2,
        rendering: false,

        async init() {
            try {
                this.pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
                this.totalPages = this.pdfDoc.numPages;
                await this.renderPage(this.currentPage);
            } catch (e) {
                console.error('PDF load error:', e);
            }
        },

        async renderPage(pageNum) {
            if (this.rendering || !this.pdfDoc) return;
            this.rendering = true;
            this.currentPage = pageNum;

            try {
                const page = await this.pdfDoc.getPage(pageNum);
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
            this.renderPage(this.currentPage);
        },

        zoomOut() {
            this.scale = Math.max(this.scale - 0.2, 0.5);
            this.renderPage(this.currentPage);
        },
    };
}
