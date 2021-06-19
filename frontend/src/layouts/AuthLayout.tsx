import { Container } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { PageComponent } from '../types/PageComponent';

const useStyles = makeStyles((theme) => ({
  paper: {
    marginTop: theme.spacing(8),
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
  },
}));

type Props = {
  children: React.ReactElement<unknown, PageComponent<unknown>>;
};

export default function AuthLayout(props: Props): JSX.Element {
  const classes = useStyles();

  return (
    <>
      <Container component="main" maxWidth="xs">
        <div className={classes.paper}>{props.children}</div>
      </Container>
    </>
  );
}
