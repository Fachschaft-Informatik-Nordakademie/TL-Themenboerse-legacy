import * as React from 'react';
import { PageComponent } from '../types/PageComponent';

type Props = {
  children: React.ReactElement<unknown, PageComponent<unknown>>;
};

export default function DefaultLayout(props: Props): JSX.Element {
  return <>{props.children}</>;
}
