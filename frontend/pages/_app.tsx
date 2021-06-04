import { CssBaseline, ThemeProvider } from '@material-ui/core';
import React from 'react';
import theme from '../src/theme';
import '../styles/globals.css'

function MyApp({ Component, pageProps }) {

  React.useEffect(() => {
    // Remove the server-side injected CSS.
    const jssStyles = document.querySelector('#jss-server-side');
    if (jssStyles) {
      jssStyles.parentElement.removeChild(jssStyles);
    }
  }, []);


  return (
    <ThemeProvider theme={theme}>
        {/* CssBaseline kickstart an elegant, consistent, and simple baseline to build upon. */}
        <CssBaseline />
        <Component {...pageProps} />
    </ThemeProvider>
  );
}

export default MyApp
