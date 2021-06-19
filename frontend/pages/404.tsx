import Head from 'next/head';
import { Button, Typography } from '@material-ui/core';
import Link from 'next/link';
import { PageComponent } from '../src/types/PageComponent';

const NotFoundPage: PageComponent<void> = (): JSX.Element => {
  return (
    <>
      <Head>
        <title>Seite nicht gefunden - Themenbörse</title>
      </Head>
      <div>
        <Typography gutterBottom variant="h4" component="h2">
          Seite nicht gefunden
        </Typography>
        <Typography gutterBottom variant="body1" component="p">
          Die angeforderte Seite existiert nicht.
        </Typography>
        <Link href="/">
          <Button variant="contained" color="primary">
            zur Startseite
          </Button>
        </Link>
      </div>
    </>
  );
};

NotFoundPage.layout = 'main';
export default NotFoundPage;
