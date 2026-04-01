import React from 'react';

export default class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, message: '', stack: '' };
  }

  static getDerivedStateFromError() {
    return { hasError: true };
  }

  componentDidCatch(error, info) {
    if (typeof console !== 'undefined' && console.error) {
      console.error('Render error:', error);
    }
    this.setState({
      message: error?.message || String(error),
      stack: info?.componentStack || '',
    });
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="bg-white shadow rounded-lg p-6">
          <h2 className="text-lg font-semibold text-gray-900">Page error</h2>
          <p className="mt-1 text-sm text-gray-600">
            Failed to render this page. Please refresh (Ctrl+F5). If it still happens, reopen from the menu.
          </p>
          <div className="mt-4 rounded-md border border-red-200 bg-red-50 p-3">
            <div className="text-sm font-medium text-red-900">Error detail</div>
            <div className="mt-1 text-xs text-red-800 break-words">{this.state.message}</div>
            {this.state.stack ? (
              <pre className="mt-2 max-h-40 overflow-auto text-[10px] leading-4 text-red-900 whitespace-pre-wrap">
                {this.state.stack}
              </pre>
            ) : null}
          </div>
          <button
            type="button"
            onClick={() => window.location.reload()}
            className="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            Reload
          </button>
        </div>
      );
    }

    return this.props.children;
  }
}
