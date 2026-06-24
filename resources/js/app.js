import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import pdfViewer from './pdf-viewer';
import { complianceBarChart, scoreDistChart, parseComparisonChart } from './charts';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.data('pdfViewer', pdfViewer);
Alpine.data('complianceBarChart', complianceBarChart);
Alpine.data('scoreDistChart', scoreDistChart);
Alpine.data('parseComparisonChart', parseComparisonChart);
Alpine.start();
