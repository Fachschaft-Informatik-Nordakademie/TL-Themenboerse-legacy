import MuiAlert, { Color } from '@material-ui/lab/Alert';
import React from 'react';

export default function createNotification(
  message: string | React.ReactNode,
  type: Color,
  className?: string,
): JSX.Element {
  return (
    <MuiAlert variant="standard" severity={type} className={className}>
      {message}
    </MuiAlert>
  );
}
