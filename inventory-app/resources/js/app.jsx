import '../css/app.css';
import './bootstrap';
import Main from './Main';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const root = createRoot(el);

root.render(<Main {...props} />);
